<?php

namespace Blackbox\Epace\Model;

abstract class AbstractObject extends Varien_Object {

    public static $debug = false;

    /**
     * @var Blackbox_Epace_Helper_Api
     */
    protected $_api;

    /**
     * @var Blackbox_Epace_Helper_Mongo
     */
    protected $_monoApi;

    /**
     * @var string
     */
    protected $_objectType;
    private $_childItems = [];
    private $_links = [];
    private $_disposing = false;
    private $global = false;

    /**
     * @var Blackbox_Epace_Model_Epace_Cache
     */
    private $_cache = null;
    public static $useMongo = false;

    public function __construct($cache = null) {
        parent::__construct();
        if (empty($this->_objectType)) {
            throw new \Exception('Object type should be initialized in _construct method.');
        }
        if ($cache instanceof Blackbox_Epace_Model_Epace_Cache) {
            $this->_cache = $cache;
        }
    }

    public function getObjectType() {
        return $this->_objectType;
    }

    public function load($id) {
        try {
            $type = $this->getDefinition()[$this->getIdFieldName()];
            switch ($type) {
                case 'string':
                default:
                    $id = (string) $id;
                    break;
                case 'int':
                    $id = (int) $id;
                    break;
                case 'date':
                    $id = new MongoDB\BSON\UTCDateTime(is_string($id) && !is_numeric($id) ? strtotime($id) : $id);
                    break;
            }

            $data = $this->getApi()->readObject($this->_objectType, [
                $this->getIdFieldName() => $id
            ]);
            $this->setData($this->_prepareLoadedData($data));

            $this->_getCache()->add(get_class($this), $id, $this);
        } catch (Blackbox_Epace_Model_Exception $e) {
            $this->unsetData();
        }
        $this->_hasDataChanges = false;

        return $this;
    }

    protected function _init($objectType, $idFieldName) {
        $this->_objectType = $objectType;
        $this->setIdFieldName($idFieldName);
    }

    /**
     * @return Blackbox_Epace_Helper_Api
     */
    public function getApi() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (self::$useMongo) {
            if (!$this->_monoApi) {
                $this->_monoApi = $objectManager->create('\Blackbox\Epace\Helper\Mongo');
            }
            return $this->_monoApi;
        } else {
            if (!$this->_api) {
                $this->_api = $objectManager->create('\Blackbox\Epace\Helper\Api');
            }
            return $this->_api;
        }
    }

    /**
     * @return array
     */
    public abstract function getDefinition();

    /**
     * @return bool
     */
    public function isGlobal() {
        return $this->global;
    }

    public function setGlobal($global) {
        $this->global = (bool) $global;

        return $this;
    }

    /**
     * Clear instance and all related instances
     *
     * @return $this
     */
    public function dispose() {
        if ($this->_disposing) {
            return $this;
        }

        $this->_disposing = true;

        try {
            if ($this->_cache) {
                $this->_cache->disposeAll();
            }

            foreach ($this->_childItems as &$items) {
                foreach ($items as $key => $item) {
                    if ($item && !$item->isGlobal()) {
                        $item->dispose();
                    }
                    unset($items[$key]);
                }
            }
            unset($this->_childItems);
            $this->_childItems = [];

            foreach ($this->_links as $key => $item) {
                if ($item && !$item->isGlobal()) {
                    $item->dispose();
                }
                unset($this->_links[$key]);
            }

            $this->unsetData();

            return $this;
        } finally {
            $this->_disposing = false;
        }
    }

    protected function _prepareLoadedData(array $data) {
        $definition = $this->getDefinition();

        foreach ($data as $key => $value) {
            if (is_null($value)) {
                continue;
            }
            switch ($definition[$key]) {
                case 'bool':
                    if (is_bool($value)) {
                        continue;
                    }
                    if ($value == 'true') {
                        $data[$key] = true;
                    } else if ($value == 'false') {
                        $data[$key] = false;
                    } else {
                        throw new Exception('Data type does not match with definition. Expected boolean. Object: ' . $this->_objectType . '. Field: ' . $key . '. Value: ' . $value);
                    }
                    break;
                case 'int':
                    if (!is_int($data[$key]) && !is_numeric($data[$key])) {
                        throw new Exception('Data type does not match with definition. Expected integer. Object: ' . $this->_objectType . '. Field: ' . $key . '. Value: ' . $value);
                    }
                    $data[$key] = (int) $data[$key];
                    break;
            }
        }

        return $data;
    }

    protected function _underscore($name) {
        return lcfirst($name); // use keys from epace as is
    }

    protected function _getObject($objectField, $dataField, $modelClass, $globalCache = false, callable $initCallback = null) {
        if (!isset($this->_links[$objectField])) {
            $this->_links[$objectField] = false;
            if (!empty($this->getData($dataField))) {
                $object = $this->_loadObject($modelClass, $this->getData($dataField), $globalCache);
                if (!is_null($object->getId())) {
                    if ($initCallback) {
                        $initCallback($object);
                    }
                    $this->_links[$objectField] = $object;
                } else if (self::$debug) {
                    throw new \Exception("Unable to load object {$object->getObjectType()} with id {$this->getData($dataField)} linked by {$this->getObjectType()} in $objectField");
                }
            }
        }

        return $this->_links[$objectField];
    }

    protected function _hasObjectField($objectField) {
        return array_key_exists($objectField, $this->_links);
    }

    protected function _getObjectField($objectField) {
        return $this->_links[$objectField];
    }

    protected function _setObject($dataField, $object) {
        $this->_links[$dataField] = $object;

        return $this;
    }

    /**
     * @param $modelClass
     * @param $id
     * @param bool $globalCache
     * @return Blackbox_Epace_Model_Epace_AbstractObject
     */
    protected function _loadObject($modelClass, $id, $globalCache = false) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        if ($globalCache) {
            $object = $objectManager->create('\Blackbox\Epace\Helper\Object')->load($modelClass, $id);
        } else {
            $object = $this->_getCache()->load($modelClass, $id);
        }
        // commented for the time being follow up
//        if (!$object) {
//            $object = Mage::getModel($modelClass);
//        }
        return $object;
    }

    protected function _getChildItems($collectionName, $filters, callable $initCallback = null) {
        if (!isset($this->_childItems[$collectionName])) {
            /** @var Blackbox_Epace_Model_Resource_Epace_Collection $collection */
            $collection = $this->_getCollection($collectionName);
            foreach ($filters as $field => $value) {
                $collection->addFilter($field, $value);
            }
            $this->_childItems[$collectionName] = $collection->getItems();
            foreach ($this->_childItems[$collectionName] as $item) {
                call_user_func($initCallback, $item);
            }
        }

        return $this->_childItems[$collectionName];
    }

    /**
     * @return Blackbox_Epace_Model_Epace_Cache
     */
    private function _getCache() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$this->_cache) {
            // no model found in magento 1. follow up
//            $this->_cache = $objectManager->create('\Blackbox\Epace\Helper\Object')
//                    Mage::getModel('efi/cache');
        }
        return $this->_cache;
    }

    /**
     * @param $collectionName
     * @return Blackbox_Epace_Model_Resource_Epace_Collection
     */
    
    // follow up
//    protected function _getCollection($collectionName) {
//        return Mage::getResourceModel($collectionName, $this->_getCache());
//    }

}
