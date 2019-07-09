<?php

namespace Blackbox\Epace\Model\Epace;

class Cache
{
    /**
     * @var Blackbox_Epace_Model_Epace_AbstractObject[][]
     */
    protected $cache = [];

    private $disposing = false;

    /**
     * @param $type
     * @param $id
     * @return Blackbox_Epace_Model_Epace_AbstractObject
     */
    public function load($type, $id)
    {
        $className = Mage::getConfig()->getModelClassName($type);
        if (!isset($this->cache[$className][$id])) {
            /** @var Blackbox_Epace_Model_Epace_AbstractObject $object */
            $object = Mage::getModel($type, $this);
            $object->load($id);
            if (is_null($object->getId())) {
                $object = false;
            }
            $this->cache[$className][$id] = $object;
        }
        return $this->cache[$className][$id];
    }

    public function add($className, $id, $object)
    {
        $this->cache[$className][$id] = $object;
    }

    public function remove($object)
    {
        foreach ($this->cache as &$items) {
            foreach ($items as $key => $item) {
                if ($item === $object) {
                    unset($items[$key]);
                    return;
                }
            }
        }
    }

    public function clear()
    {
        unset($this->cache);
        $this->cache = [];
    }

    public function disposeAll()
    {
        if ($this->disposing) {
            return;
        }
        $this->disposing = true;

        try {
            foreach ($this->cache as &$items) {
                foreach ($items as $key => $item) {
                    if ($item) {
                        $item->dispose();
                    }
                }
            }
            $this->clear();
        } finally {
            $this->disposing = false;
        }
    }
}