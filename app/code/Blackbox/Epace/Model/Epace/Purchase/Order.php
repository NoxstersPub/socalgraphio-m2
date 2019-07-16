<?php

namespace Blackbox\Epace\Model\Epace\Purchase;

class Order extends \Blackbox\Epace\Model\Epace\EpaceObject
{
    protected function _construct()
    {
        $this->_init('PurchaseOrder', 'id');
    }

    /**
     * @return string
     */
    public function getStateCode()
    {
        return $this->getData('state');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\State
     */
    public function getState()
    {
        return $this->_getObject('state', 'stateKey', 'efi/state', true);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Country
     */
    public function getCountry()
    {
        return $this->_getObject('country', 'country', 'efi/country', true);
    }

    /**
     * @return string
     */
    public function getVendorId()
    {
        return $this->getData('vendor');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Vendor
     */
    public function getVendor()
    {
        return $this->_getObject('vendor', 'vendor', 'efi/vendor');
    }

    /**
     * @return int
     */
    public function getVendorContactId()
    {
        return $this->getData('vendorContact');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Contact
     */
    public function getVendorContact()
    {
        return $this->_getObject('vendorContact', 'vendorContact', 'efi/contact');
    }

    /**
     * @return int
     */
    public function getShipViaId()
    {
        return $this->getData('shipVia');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Ship_Via
     */
    public function getShipVia()
    {
        return $this->_getObject('shipVia', 'shipVia', 'efi/ship_via', true);
    }

    /**
     * @return string
     */
    public function getOrderStatusId()
    {
        return $this->getData('orderStatus');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\POStatus
     */
    public function getOrderStatus()
    {
        return $this->_getObject('orderStatus', 'orderStatus', 'efi/pOStatus', true);
    }

    /**
     * @return int
     */
    public function getShipToContactId()
    {
        return $this->getData('shipToContact');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Contact
     */
    public function getShipToContact()
    {
        return $this->_getObject('shipToContact', 'shipToContact', 'efi/contact');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Purchase_Order_Type
     */
    public function getType()
    {
        return $this->_getObject('purchaseOrderType', 'purchaseOrderType', 'efi/purchase_order_type', true);
    }

    /**
     * @return string
     */
    public function getAltCurrencyCode()
    {
        return $this->getData('altCurrency');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Currency
     */
    public function getAltCurrency()
    {
        return $this->_getObject('altCurrency', 'altCurrency', 'efi/currency', true);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Purchase_Order_Line[]
     */
    public function getLines()
    {
        return $this->_getChildItems('efi/purchase_order_line_collection', [
            'purchaseOrder' => $this->getId()
        ], function(\Blackbox\Epace\Model\Epace\Purchase\Order\Line $line) {
            $line->setPurchaseOrder($this);
        });
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'companyName' => 'string',
            'contactFirstName' => 'string',
            'contactLastName' => 'string',
            'address1' => 'string',
            'address2' => 'string',
            'address3' => 'string',
            'city' => 'string',
            'zip' => 'string',
            'state' => 'string',
            'country' => 'int',
            'emailAddress' => 'string',
            'phoneNumber' => 'string',
            'poNumber' => 'string',
            'vendor' => 'string',
            'vendorContact' => 'int',
            'terms' => 'int',
            'shipVia' => 'int',
            'orderStatus' => 'string',
            'discountCode' => 'bool',
            'dateEntered' => 'date',
            'orderTotal' => 'float',
            'originalTotal' => 'float',
            'discountAmount' => 'float',
            'createdBy' => 'string',
            'requester' => 'string',
            'shipToContact' => 'int',
            'purchaseOrderType' => 'int',
            'taxBase1' => 'float',
            'taxBase2' => 'float',
            'taxAmount1' => 'float',
            'taxAmount2' => 'float',
            'freightOnBoard' => 'string',
            'altCurrency' => 'string',
            'altCurrencyRate' => 'float',
            'altCurrencyRateSource' => 'string',
            'altCurrencyRateSourceNote' => 'string',
            'convertEnteredValues' => 'bool',
            'printStreamShared' => 'bool',
            'poShared' => 'bool',
            'authorizationRequested' => 'bool',
            'receiveDate' => 'date',
            'taxedTotal' => 'float',
            'totalLines' => 'int',
            'stateKey' => 'string',
        ];
    }
}