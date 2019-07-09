<?php

namespace Blackbox\Epace\Model\Epace\Purchase\Order;

class Line extends \Blackbox\Epace\Model\Epace\Job\Part\AbstractChild
{
    const LINE_TYPE_MANUAL = 1;
    const LINE_TYPE_INVENTORY = 3;
    const LINE_TYPE_DESCRIPTION = 4;

    public function _construct()
    {
        $this->_init('PurchaseOrderLine', 'id');
    }

    /**
     * @return int
     */
    public function getPurchaseOrderId()
    {
        return $this->getData('purchaseOrder');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Purchase_Order
     */
    public function getPurchaseOrder()
    {
        return $this->_getObject('purchaseOrder', 'purchaseOrder', 'efi/purchase_order');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Purchase_Order $order
     * @return $this
     */
    public function setPurchaseOrder(\Blackbox\Epace\Model\Epace\Purchase\Order $order)
    {
        return $this->_setObject('purchaseOrder', $order);
    }

    /**
     * @return int
     */
    public function getJobPartOutsidePurchId()
    {
        return $this->getData('jobPartOutsidePurch');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Part_OutsidePurch
     */
    public function getJobPartOutsidePurch()
    {
        return $this->_getObject('jobPartOutsidePurch', 'jobPartOutsidePurch', 'efi/job_part_outsidePurch');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Job_Part_OutsidePurch $jobPartOutsidePurch
     * @return $this
     */
    public function setJobPartOutsidePurch(\Blackbox\Epace\Model\Epace\Job_Part\OutsidePurch $jobPartOutsidePurch)
    {
        return $this->_setObject('jobPartOutsidePurch', $jobPartOutsidePurch);
    }

    /**
     * @return string
     */
    public function getActivityCodeId()
    {
        return $this->getData('activityCode');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Activity_Code
     */
    public function getActivityCode()
    {
        return $this->_getObject('activityCode', 'activityCode', 'efi/activity_code');
    }

    /**
     * @return string
     */
    public function getSalesTaxRate1Code()
    {
        return $this->getData('salesTaxRate1');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\SalesTax
     */
    public function getSalesTaxRate1()
    {
        return $this->_getObject('salesTaxRate1', 'salesTaxRate1', 'efi/salesTax', true);
    }

    /**
     * @return string
     */
    public function getSalesTaxRate2Code()
    {
        return $this->getData('salesTaxRate2');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\SalesTax
     */
    public function getSalesTaxRate2()
    {
        return $this->_getObject('salesTaxRate2', 'salesTaxRate2', 'efi/salesTax', true);
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'purchaseOrder' => 'int',
            'poNumber' => 'string',
            'uom' => 'string',
            'qtyUom' => 'string',
            'lineType' => 'int',
            'unitPrice' => 'float',
            'description' => 'string',
            'lineStatus' => 'string',
            'qtyOrdered' => 'float',
            'job' => 'string',
            'jobPart' => 'string',
            'activityCode' => 'string',
            'glAccount' => 'int',
            'jobPartOutsidePurch' => 'int',
            'taxable' => 'bool',
            'dateEntered' => 'date',
            'printStreamShared' => 'bool',
            'externalLineNum' => 'int',
            'paperSheet' => 'bool',
            'salesTaxRate1' => 'string',
            'taxBase1' => 'float',
            'taxBase1Forced' => 'bool',
            'taxAmount1' => 'float',
            'taxAmount1Forced' => 'bool',
            'salesTaxRate2' => 'string',
            'taxBase2' => 'float',
            'taxBase2Forced' => 'bool',
            'taxAmount2' => 'float',
            'taxAmount2Forced' => 'bool',
            'extendedPrice' => 'float',
            'quantityToReceive' => 'float',
            'totalWeight' => 'string',
            'JobPartKey' => 'string',
        ];
    }
}