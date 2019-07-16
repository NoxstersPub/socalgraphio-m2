<?php

namespace Blackbox\Epace\Model\Epace;

class Invoice extends \Blackbox\Epace\Model\Epace\Job\Part\AbstractChild
{
    protected function _construct()
    {
        $this->_init('Invoice', 'id');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Invoice_Batch|bool
     */
    public function getBatch()
    {
        return $this->_getObject('batch', 'invoiceBatch', 'efi/invoice_batch');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Invoice_Batch $batch
     * @return $this
     */
    public function setBatch(\Blackbox\Epace\Model\Epace\Invoice\Batch $batch)
    {
        return $this->_setObject('batch', $batch);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\SalesCategory|false
     */
    public function getSalesCategory()
    {
        return $this->_getObject('salesCategory', 'salesCategory', 'efi/salesCategory', true);
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\SalesCategory $salesCategory
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
     * @return \Blackbox\Epace\Model\Epace\SalesTax
     */
    public function getSalesTax()
    {
        return $this->_getObject('salesTax', 'salesTax', 'efi/salesTax', true);
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\SalesTax $salesTax
     * @return $this
     */
    public function setSalesTax(\Blackbox\Epace\Model\Epace\SalesTax $salesTax)
    {
        return $this->_setObject('salesTax', $salesTax);
    }

    /**
     * @return int
     */
    public function getReceivableId()
    {
        return $this->getData('receivable');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Receivable
     */
    public function getReceivable()
    {
        return $this->_getObject('receivable', 'receivable', 'efi/receivable', false, function (\Blackbox\Epace\Model\Epace\Receivable $receivable) {
            $receivable->setInvoice($this);
        });
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Receivable $receivable
     * @return $this
     */
    public function setReceivable(\Blackbox\Epace\Model\Epace\Receivable $receivable)
    {
        return $this->_setObject('receivable', $receivable);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Invoice_CommDist[]
     */
    public function getCommDists()
    {
        return $this->_getInvoiceChildren('efi/invoice_commDist_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Invoice_Extra[]
     */
    public function getExtras()
    {
        return $this->_getInvoiceChildren('efi/invoice_extra_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Invoice_Line[]
     */
    public function getLines()
    {
        return $this->_getInvoiceChildren('efi/invoice_line_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Invoice_SalesDist[]
     {
       
    public function getSalesDists()
    {
        return $this->_getInvoiceChildren('efi/invoice_salesDist_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Invoice_TaxDist[]
     */
    public function getTaxDists()
    {
        return $this->_getInvoiceChildren('efi/invoice_taxDist_collection');
    }

    public function getDefinition()
    {
        return [
            'partiallyBill' => 'bool',
            'id' => 'int',
            'invoiceBatch' => 'int',
            'job' => 'string',
            'jobPart' => 'string',
            'invoiceNum' => 'string',
            'invoiceDate' => 'date',
            'invoiceType' => '',
            'salesCategory' => 'int',
            'salesTax' => 'string',
            'taxableCode' => '',
            'shipVia' => 'int',
            'terms' => '',
            'salesPerson' => 'int',
            'shipToContact' => 'int',
            'previousAdminStatus' => '',
            'previousProductionStatus' => '',
            'contactFirstName' => '',
            'contactLastName' => '',
            'shipToFormat' => '',
            'taxAmount' => '',
            'commissionAmount' => '',
            'lineItemTotal' => '',
            'commissionBase' => '',
            'commissionBaseAdjustmentForced' => 'bool',
            'commissionAmountAdjustment' => '',
            'taxBase' => '',
            'taxBaseAdjustmentForced' => 'bool',
            'taxAmountAdjustment' => '',
            'totalCost' => '',
            'targetSell' => '',
            'salesDistributionMethod' => '',
            'distributeCommission' => 'bool',
            'lockTaxAmount' => 'bool',
            'lockCommissionAmount' => 'bool',
            'lockCommissionBase' => 'bool',
            'lockTaxBase' => 'bool',
            'lockCustomerDiscount' => 'bool',
            'postCompleted' => 'bool',
            'valueAdded' => '',
            'valueAddedForced' => 'bool',
            'valueAddedCost' => '',
            'receivable' => 'int',
            'altCurrency' => '',
            'altCurrencyRate' => '',
            'altCurrencyRateSource' => '',
            'altCurrencyRateSourceNote' => 'string',
            'percentWipToRelieve' => '',
            'balanced' => 'bool',
            'sendAsPreInvoice' => 'bool',
            'discountBase' => '',
            'discountBaseForced' => 'bool',
            'commissionSalesCategory' => '',
            'nextLineNum' => '',
            'commissionRate' => '',
            'exportedTo3rdParty' => 'bool',
            'memoApproved' => 'bool',
            'memoCommitted' => 'bool',
            'enteredBy' => '',
            'posting' => 'bool',
            'calculating' => 'bool',
            'review' => 'bool',
            'distributionRemaining' => '',
            'closeJob' => '',
            'excludeFromConsolidation' => 'bool',
            'dateSetup' => 'date',
            'timeSetup' => 'date',
            'manufacturingLocation' => '',
            'taxDistributionMethod' => '',
            'taxDistributionSource' => '',
            'commissionDistributionMethod' => '',
            'commissionDistributionSource' => '',
            'salesTaxBasis' => '',
            'posted' => 'bool',
            'invoiceAmount' => '',
            'invoiceAmountAdjustment' => '',
            'memoAdjustment' => '',
            'adjustedInvoiceAmount' => '',
            'balanceAmount' => '',
            'freightAmount' => '',
            'totalExtras' => '',
            'depositAmount' => '',
            'quantityShipped' => '',
            'quantityOrdered' => '',
            'useVAT' => 'bool',
            'distributeTax' => 'bool',
            'quickInvoice' => 'bool',
            'showQuickInvoiceReport' => 'bool',
            'taxingRequired' => 'bool',
            'JobPartKey' => 'string',
        ];
    }

    protected function _getInvoiceChildren($collectionName)
    {
        return $this->_getChildItems($collectionName, [
            'invoice' => $this->getId()
        ], function ($item) {
            $item->setInvoice($this);
        });
    }
}