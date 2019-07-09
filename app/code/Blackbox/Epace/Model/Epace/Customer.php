<?php

namespace Blackbox\Epace\Model\Epace;

class Customer extends \Blackbox\Epace\Model\Epace\AbstractObject
{
    protected function _construct()
    {
        $this->_init('Customer', 'id');
    }

    /**
     * @return \Blackbox\Epace\Model\SalesPerson|bool
     */
    public function getSalesPerson()
    {
        return $this->_getObject('salesPerson', 'salesPerson', 'efi/salesPerson', true);
    }

    /**
     * @param \Blackbox\Epace\Model\SalesPerson $salesPerson
     * @return $this
     */
    public function setSalesPerson(\Blackbox\Epace\Model\Epace\SalesPerson $salesPerson)
    {
        return $this->_setObject('salesPerson', $salesPerson);
    }

    /**
     * @return \Blackbox\Epace\Model\CSR|bool
     */
    public function getCSR()
    {
        return $this->_getObject('csr', 'csr', 'efi/cSR', true);
    }

    /**
     * @param \Blackbox\Epace\Model\CSR $csr
     * @return $this
     */
    public function setCSR(\Blackbox\Epace\Model\Epace\CSR $csr)
    {
        return $this->_setObject('csr', $csr);
    }

    /**
     * @return \Blackbox\Epace\Model\Country|bool
     */
    public function getCountry()
    {
        return $this->_getObject('country', 'country', 'efi/country', true);
    }

    public function setCountry(\Blackbox\Epace\Model\Epace\Country $country)
    {
        return $this->_setObject('country', $country);
    }

    /**
     * @return \Blackbox\Epace\Model\SalesCategory|false
     */
    public function getSalesCategory()
    {
        return $this->_getObject('salesCategory', 'salesCategory', 'efi/salesCategory', true);
    }

    /**
     * @param \Blackbox\Epace\Model\SalesCategory $salesCategory
     * @return $this
     */
    public function setSalesCategory(\Blackbox\Epace\Model\Epace\SalesCategory $salesCategory)
    {
        return $this->_setObject('salesCategory', $salesCategory);
    }

    /**
     * @return string
     */
    public function getSalesTaxCode()
    {
        return $this->getData('salesTax');
    }

    /**
     * @return \Blackbox\Epace\Model\SalesTax
     */
    public function getSalesTax()
    {
        return $this->_getObject('salesTax', 'salesTax', 'efi/salesTax', true);
    }

    /**
     * @param \Blackbox\Epace\Model\SalesTax $salesTax
     * @return $this
     */
    public function setSalesTax(\Blackbox\Epace\Model\Epace\SalesTax $salesTax)
    {
        return $this->_setObject('salesTax', $salesTax);
    }

    /**
     * @return \Blackbox\Epace\Model\Ship_Via|bool
     */
    public function getShipVia()
    {
        return $this->_getObject('shipVia', 'shipVia', 'efi/ship_via');
    }

    /**
     * @param \Blackbox\Epace\Model\Ship_Via $shipVia
     * @return $this
     */
    public function setShipVia(\Blackbox\Epace\Model\Epace\Ship\Via $shipVia)
    {
        return $this->_setObject('shipVia', $shipVia);
    }

    /**
     * @return \Blackbox\Epace\Model\Contact|bool
     */
    public function getShipToContact()
    {
        return $this->_getObject('shipToContact', 'shipToContact', 'efi/contact');
    }

    /**
     * @param \Blackbox\Epace\Model\Contact $contact
     * @return $this
     */
    public function setShipToContact(\Blackbox\Epace\Model\Epace\Contact $contact)
    {
        return $this->_setObject('shipToContact', $contact);
    }

    public function getDefinition()
    {
        return [
            'id' => 'string',
            'custName' => 'string',
            'accountBalance' => 'float',
            'customerStatus' => 'string',
            'address1' => 'string',
            'address2' => 'string',
            'address3' => 'string',
            'aging1' => '',
            'aging2' => '',
            'aging3' => '',
            'aging4' => '',
            'agingCurrent' => '',
            'agingServiceCharge1' => '',
            'agingServiceCharge2' => '',
            'agingServiceCharge3' => '',
            'agingServiceCharge4' => '',
            'agingServiceChargeCurrent' => '',
            'avgPaymentDays' => '',
            'city' => 'string',
            'customerType' => 'int',
            'contactFirstName' => '',
            'contactLastName' => '',
            'country' => 'int',
            'creditLimit' => '',
            'csr' => 'int',
            'dateHighBalance' => 'date',
            'dateLastInvoice' => 'date',
            'dateLastPayment' => 'date',
            'dateSetup' => 'date',
            'defaultDaysUntilJobDue' => '',
            'highestBalance' => '',
            'email' => '',
            'orderAlert' => 'bool',
            'phoneNumber' => '',
            'salesCategory' => 'int',
            'salesPerson' => 'int',
            'salesTax' => 'string',
            'salesYTD' => '',
            'shipVia' => 'int',
            'state' => '',
            'statementCycle' => '',
            'taxableCode' => '',
            'terms' => '',
            'wipBalance' => '',
            'zip' => '',
            'creditCardProcessingEnabled' => 'bool',
            'shipToFormat' => '',
            'nextServiceChargeDate' => 'date',
            'applyDiscountToInvoice' => 'bool',
            'calculateTax' => 'bool',
            'calculateFreight' => 'bool',
            'displayPrice' => 'bool',
            'defaultQuoteLetterType' => '',
            'shipInNameOf' => '',
            'defaultJob' => '',
            'defaultCurrency' => '',
            'allowFailedFreightCheckout' => 'bool',
            'plantManagerId' => '',
            'dsfShared' => 'bool',
            'defaultContact' => '',
            'requireBillOfLadingPerJob' => 'bool',
            'dsfCustomer' => 'bool',
            'useAlternateText' => 'bool',
            'autoAddContact' => 'bool',
            'printStreamShared' => 'bool',
            'printStreamCustomer' => 'bool',
            'billToAlt' => 'bool',
            'shipBillToAlt' => 'bool',
            'defaultAlt' => 'bool',
            'shipToAlt' => 'bool',
            'defaultDsfContact' => '',
            'invoiceDeliveryMethod' => '',
            'statementDeliveryMethod' => '',
            'processPrintStreamItems' => '',
            'sageAccountingEnabled' => 'bool',
            'jeevesAccountingEnabled' => 'bool',
            'agingTotal' => '',
            'aging1Percent' => '',
            'aging2Percent' => '',
            'aging3Percent' => '',
            'aging4Percent' => '',
            'customerTypeAgingTotalPercent' => '',
            'unpostedPaymentBalance' => '',
            'probability' => '',
            'stateKey' => 'string',
        ];
    }
}