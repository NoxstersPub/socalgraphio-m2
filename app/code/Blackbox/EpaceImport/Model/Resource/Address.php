<?php

/**
 * Flat purchase order address resource
 */
class Blackbox_EpaceImport_Model_Resource_Address extends Blackbox_EpaceImport_Model_Resource_PurchaseOrder_Abstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'epacei_address_resource';

    /**
     * Resource initialization
     *
     */
    protected function _construct()
    {
        $this->_init('epacei/address', 'address_id');
    }

    /**
     * Return configuration for all attributes
     *
     * @return array
     */
    public function getAllAttributes()
    {
        $attributes = array(
            'city'       => Mage::helper('sales')->__('City'),
            'company'    => Mage::helper('sales')->__('Company'),
            'country_id' => Mage::helper('sales')->__('Country'),
            'email'      => Mage::helper('sales')->__('Email'),
            'firstname'  => Mage::helper('sales')->__('First Name'),
            'middlename' => Mage::helper('sales')->__('Middle Name'),
            'lastname'   => Mage::helper('sales')->__('Last Name'),
            'region_id'  => Mage::helper('sales')->__('State/Province'),
            'street'     => Mage::helper('sales')->__('Street Address'),
            'telephone'  => Mage::helper('sales')->__('Telephone'),
            'postcode'   => Mage::helper('sales')->__('Zip/Postal Code'),
        );
        asort($attributes);
        return $attributes;
    }
}
