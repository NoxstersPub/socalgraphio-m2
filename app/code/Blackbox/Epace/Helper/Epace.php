<?php

namespace Blackbox\Epace\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Epace extends \Magento\Framework\App\Helper\AbstractHelper{
    
    protected $_settingsPath = 'epace/main_settings/';
    protected $_storeManager;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
        ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_settingsPath =  $this->scopeConfig->getValue('epace/main_settings/', \Magento\Store\Model\ScopeInterface::SCOPE_STORE,null);
    }

    public function isEnabled()
    {
        return $this->scopeConfig->getValue($this->_settingsPath . 'enable');
    }

    public function isLiveMode()
    {
        return $this->scopeConfig->getValue($this->_settingsPath . 'mode');
    }

    public function getTypeName($objectType)
    {
        if ($objectType == 'EstimateQuoteLetterNote') {
            return 'estimate_quoteLetter_note';
        } else if ($objectType == 'FinishingOperationSpeed') {
            return 'finishingOperation_speed';
        }
        $re = '/(?(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z]))/x';
        $matches = preg_split($re, $objectType);
        $matches = array_map('lcfirst', $matches);
        $count = count($matches);
        for ($i = 0; $i < $count; $i++) {
            $type = implode('_', $matches);
            $object = $objectManager->create('\Blackbox\Epace\Model\''. $type);
//            $object = Mage::getModel('efi/' . $type);
            if ($object) {
                return $type;
            }

            if ($count - $i > 1) {
                $matches[$count - $i - 2] = $matches[$count - $i - 2] . ucfirst($matches[$count - $i - 1]);
                unset($matches[$count - $i - 1]);
            }
        }

        return null;
    }
}