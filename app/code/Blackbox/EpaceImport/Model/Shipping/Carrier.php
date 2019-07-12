<?php

class Blackbox_EpaceImport_Model_Shipping_Carrier extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'epace_shipping';

    protected $allowedMethods = null;

    const STORE_CONFIG_EPACE_SHIPPING_CACHE_KEY = 'epacei/shipping/cache';

    public function getAllowedMethods()
    {
        if (is_null($this->allowedMethods)) {
            $this->allowedMethods = [
                'empty' => 'Empty'
            ];

            try {
                $cache = Mage::getStoreConfig(self::STORE_CONFIG_EPACE_SHIPPING_CACHE_KEY);
                if ($cache) {
                    $cache = json_decode($cache, true);
                }

                /** @var Blackbox_EpaceImport_Helper_Data $helper */
                $helper = Mage::helper('epacei');

                if ($cache && $cache['timestamp'] && time() - $cache['timestamp'] < 86400 && !empty($cache['allowedMethods'])) {
                    $this->allowedMethods = $cache['allowedMethods'];
                } else {
                    /** @var Blackbox_Epace_Model_Resource_Epace_Ship_Provider_Collection $collection */
                    $collection = Mage::getResourceModel('efi/ship_provider_collection');
                    foreach ($collection->getItems() as $provider) {
                        foreach ($provider->getShipVias() as $shipVia) {
                            $this->allowedMethods[$helper->getShipViaMethodCode($shipVia)] = $provider->getName() . ' - ' . $shipVia->getDescription();
                        }
                    }

                    Mage::getConfig()->saveConfig(self::STORE_CONFIG_EPACE_SHIPPING_CACHE_KEY, json_encode([
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
        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');

        foreach ($this->getAllowedMethods() as $method => $label)
        {
            /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
            $rate = Mage::getModel('shipping/rate_result_method');

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
        $rate = Mage::getModel('shipping/rate_result_method');
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('empty');
        $rate->setMethodTitle('Empty');

        return $rate;
    }
}