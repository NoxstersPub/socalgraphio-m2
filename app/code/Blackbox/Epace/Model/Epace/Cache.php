<?php

namespace Blackbox\Epace\Model\Epace;

class Cache
{
    /**
     * @var \Blackbox\Epace\Model\Epace\EpaceObject[][]
     */
    protected $cacheEnabled = true;

    protected $cache = [];

    public function getCacheEnabled()
    {
        return $this->cacheEnabled;
    }

    public function setCacheEnabled($enabled)
    {
        $this->cacheEnabled = (bool) $enabled;

        return $this;
    }
    private $disposing = false;

    /**
     * @param $type
     * @param $id
     * @return \Blackbox\Epace\Model\Epace\EpaceObject
     */
    public function load($type, $id)
    {

        /** 
         * It is implemented as Magento 1 standards
         */
            
        // need follow up here too.
        if ($this->cacheEnabled) {
            if (isset($this->cache[$type][$id])) {
                return $this->cache[$type][$id];
            }
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $model = $objectManager->create($type);
            return $this->cache[$type][$id] = $model->setGlobal(true)->load($id);
        } else {
            return $objectManager->create($type)->load($id);
        }
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