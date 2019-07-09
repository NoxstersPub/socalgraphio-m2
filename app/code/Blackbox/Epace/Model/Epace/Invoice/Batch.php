<?php

namespace Blackbox\Epace\Model\Epace\Invoice;

class Batch extends \Blackbox\Epace\Model\Epace\AbstractObject
{
    protected function _construct()
    {
        $this->_init('InvoiceBatch', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'glAccountingPeriod' => '',
            'date' => 'date',
            'enteredBy' => '',
            'description' => '',
            'approved' => 'bool',
            'manual' => 'bool',
            'status' => 'int',
            'dateSetup' => 'date',
            'timeSetup' => 'date',
            'posted' => 'bool',
            'isExportedToThirdParty' => 'bool',
            'invoiceCount' => '',
            'invoiceSum' => '',
        ];
    }
}