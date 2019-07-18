<?php

namespace Blackbox\EpaceImport\Model\Resource;

class Address extends \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\PurchaseOrderAbstract
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
            'city'       => 'City',
            'company'    => 'Company',
            'country_id' => 'Country',
            'email'      => 'Email',
            'firstname'  => 'First Name',
            'middlename' => 'Middle Name',
            'lastname'   => 'Last Name',
            'region_id'  => 'State/Province',
            'street'     => 'Street Address',
            'telephone'  => 'Telephone',
            'postcode'   => 'Zip/Postal Code',
        );
        asort($attributes);
        return $attributes;
    }
}
