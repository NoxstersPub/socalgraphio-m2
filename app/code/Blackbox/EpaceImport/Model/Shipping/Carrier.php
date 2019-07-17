<?php

namespace Blackbox\EpaceImport\Model\Shipping;

class Carrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier
    implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    protected $_code = 'epace_shipping';

    protected $allowedMethods = null;

    const STORE_CONFIG_EPACE_SHIPPING_CACHE_KEY = 'epacei/shipping/cache';

    public function getAllowedMethods()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $helper = $objectManager->create('Blackbox\EpaceImport\Helper\EpaceImport');
        if (is_null($this->allowedMethods)) {
            $this->allowedMethods = [
                'empty' => 'Empty'
            ];

            try {
                $cache = $storeManager->getValue(self::STORE_CONFIG_EPACE_SHIPPING_CACHE_KEY);
                if ($cache) {
                    $cache = json_decode($cache, true);
                }

                /** @var Blackbox_EpaceImport_Helper_Data $helper */

                if ($cache && $cache['timestamp'] && time() - $cache['timestamp'] < 86400 && !empty($cache['allowedMethods'])) {
                    $this->allowedMethods = $cache['allowedMethods'];
                } else {
                    /** @var Blackbox_Epace_Model_Resource_Epace_Ship_Provider_Collection $collection */
                    $collection = $storeManager->create('Blackbox\Epace\Model\Resource\Epace\Ship\Provider\Collection');
                    foreach ($collection->getItems() as $provider) {
                        foreach ($provider->getShipVias() as $shipVia) {
                            $this->allowedMethods[$helper->getShipViaMethodCode($shipVia)] = $provider->getName() . ' - ' . $shipVia->getDescription();
                        }
                    }

                    $storeManager->saveConfig(self::STORE_CONFIG_EPACE_SHIPPING_CACHE_KEY, json_encode([
                        'timestamp' => time(),
                        'allowedMethods' => $this->allowedMethods
                    ]));
                }
            } catch (\Exception $e) {

            }
        }

        return $this->allowedMethods;
    }

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        $helper = $objectManager->create('Blackbox\EpaceImport\Helper\EpaceImport');
        
        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = $objectManager->create('Magento\Shipping\Model\Rate\Result');
        foreach ($this->getAllowedMethods() as $method => $label)
        {
            /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
            $rate = $objectManager->create('\Magento\Quote\Model\Quote\Address\Rate');
            $rate->setCarrier($this->_code);
            $rate->setCarrierTitle($this->getConfigData('title'));
            $rate->setMethod($method);
            $rate->setMethodTitle($label);
            $rate->setPrice(0);
            $rate->setCost(0);

            $result->append($rate);
        }

        return $result;
    }

    /**
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    public function getEmptyRate()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        $rate = $objectManager->create('\Magento\Quote\Model\Quote\Address\Rate');
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('empty');
        $rate->setMethodTitle('Empty');

        return $rate;
    }
}