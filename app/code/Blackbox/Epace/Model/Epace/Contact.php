<?php

namespace Blackbox\Epace\Model\Epace;

class Contact extends \Blackbox\Epace\Model\Epace\EpaceObject
{
    protected function _construct()
    {
        $this->_init('Contact', 'id');
    }

    /**
     * @return Blackbox_Epace_Model_Epace_Customer|bool
     */
    public function getCustomer()
    {
        return $this->_getObject('customer', 'customer', 'efi/customer', true);
    }

    public function setCustomer(\Blackbox\Epace\Model\Epace\Customer $customer)
    {
        return $this->_setObject('customer', $customer);
    }

    /**
     * @return Blackbox_Epace_Model_Epace_SalesPerson|bool
     */
    public function getSalesPerson()
    {
        return $this->_getObject('salesPerson', 'salesPerson', 'efi/salesPerson', true);
    }

    /**
     * @param Blackbox_Epace_Model_Epace_SalesPerson $salesPerson
     * @return $this
     */
    /**
     * It was argumented as Magento 1 standards
     */
    public function setSalesPerson(\Blackbox\Epace\Model\Epace\SalesPerson $salesPerson)
    {
        return $this->_setObject('salesPerson', $salesPerson);
    }

    /**
     * @return Blackbox_Epace_Model_Epace_Country|bool
     */
    public function getCountry()
    {
        return $this->_getObject('country', 'country', 'efi/country', true);
    }

    //  It was argumented as Magento 1 standards
    public function setCountry(\Blackbox\Epace\Model\Epace\Country $country)
    {
        return $this->_setObject('country', $country);
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'lookupHint' => 'string',
            'firstName' => 'string',
            'lastName' => 'string',
            'businessPhoneNumber' => 'string',
            'businessPhoneExtension' => 'string',
            'email' => 'string',
            'businessFaxNumber' => 'string',
            'businessFaxExtension' => 'string',
            'mobilePhoneNumber' => 'string',
            'otherPhoneNumber ' => 'string',
            'title' => 'string',
            'companyName' => 'string',
            'address1' => 'string',
            'address2' => 'string',
            'address3' => 'string',
            'city' => 'string',
            'state' => 'string',
            'zip' => 'string',
            'country' => 'int',
            'homePhoneNumber ' => 'string',
            'homeFaxNumber' => 'string',
            'customer' => 'string',
            'residential' => 'bool',
            'active' => 'bool',
            'prospect' => 'bool',
            'doNotCall' => 'bool',
            'doNotEmail' => 'bool',
            'needsInfo' => 'bool',
            'crm' => 'bool',
            'defaultCurrency' => 'string',
            'autoUpdate' => 'bool',
            'jobContact' => 'bool',
            'failedGPSLookup' => 'bool',
            'metroAreaForced' => 'bool',
            'useAlternateText' => 'bool',
            'imUserName' => 'string',
            'dsfShipTo' => 'bool',
            'dsfUser' => 'bool',
            'altAutoUpdate' => 'bool',
            'altBill' => 'bool',
            'billTo' => 'bool',
            'shipTo' => 'bool',
            'globalContact' => 'bool',
            'stateKey' => 'string',
        ];
    }
}