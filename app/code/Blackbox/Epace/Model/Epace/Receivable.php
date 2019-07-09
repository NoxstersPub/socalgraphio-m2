<?php

namespace Blackbox\Epace\Model\Epace;

class Receivable extends \Blackbox\Epace\Model\Epace\Job\Part\AbstractChild
{
    protected function _construct()
    {
        $this->_init('Receivable', 'id');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Customer
     */
    public function getCustomer()
    {
        return $this->_getObject('customer', 'customer', 'efi/customer');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Customer $customer
     * @return $this
     */
    public function setCustomer(\Blackbox\Epace\Model\Epace\Customer $customer)
    {
        return $this->_setObject('customer', $customer);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Invoice|bool
     */
    public function getInvoice()
    {
        if ($this->getAltCurrencyRateSource() == 'Invoice') {
            return $this->_getObject('invoice', 'altCurrencyRateSourceNote', 'efi/invoice');
        } else if ($this->_hasObjectField('invoice')) {
            return $this->_getObjectField('invoice');
        } else {
            return false;
        }
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Invoice $invoice
     * @return $this
     */
    public function setInvoice(\Blackbox\Epace\Model\Epace\Invoice $invoice)
    {
        return $this->_setObject('invoice', $invoice);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Receivable_Line[]
     */
    public function getLines()
    {
        return $this->_getChildItems('efi/receivable_line_collection', [
            'receivable' => $this->getId()
        ], function (\Blackbox\Epace\Model\Epace\Receivable\Line $line) {
            $line->setReceivable($this);
        });
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'customer' => 'string',
            'status' => 'int',
            'invoiceNumber' => 'string',
            'job' => 'string',
            'jobPart' => 'string',
            'invoiceDate' => 'date',
            'dueDate' => 'date',
            'expectedPaymentDate' => 'date',
            'taxAmount' => '',
            'targetSell' => '',
            'taxableCode' => '',
            'taxBase' => '',
            'commissionAmount' => '',
            'commissionBase' => '',
            'customerType' => '',
            'glAccountingPeriod' => '',
            'description' => '',
            'amountDue' => '',
            'originalAmount' => '',
            'invoiceAmount' => '',
            'freightAmount' => '',
            'discountDate' => 'date',
            'discountAvailable' => '',
            'salesCategory' => '',
            'dateCommissionPaid' => 'date',
            'datePaidOff' => 'date',
            'orginalBatchId' => '',
            'altCurrency' => '',
            'altCurrencyRate' => 'float',
            'altCurrencyRateSource' => 'string',
            'altCurrencyRateSourceNote' => 'string',
            'glRegisterNumber' => 'string',
            'commissionRate' => 'float',
            'dateSetup' => 'date',
            'timeSetup' => 'date',
            'sendDunningLetter' => 'bool',
            'agingCategory' => '',
            'taxRate1Amount' => 'float',
            'taxRate2Amount' => 'float',
            'taxRate3Amount' => 'float',
            'taxRate4Amount' => 'float',
            'taxRate5Amount' => 'float',
            'taxRate6Amount' => 'float',
            'taxRate7Amount' => 'float',
            'discountApplied' => 'float',
            'unpaidAmount' => 'float',
            'availRemainingDeposit' => 'float',
        ];
    }
}