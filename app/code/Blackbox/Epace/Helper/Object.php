<?php
namespace Blackbox\Epace\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Object extends \Magento\Framework\App\Helper\AbstractHelper
{
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

    public function load($type, $id)
    {
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
}