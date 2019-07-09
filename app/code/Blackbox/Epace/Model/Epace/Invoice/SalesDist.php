<?php

namespace Blackbox\Epace\Model\Epace\Invoice;

class SalesDist extends \Blackbox\Epace\Model\Epace\Invoice\ChildAbstract
{
    protected function _construct()
    {
        $this->_init('InvoiceSalesDist', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'invoice' => 'int',
            'amount' => '',
            'salesCategory' => '',
            'memoCreated' => 'bool',
            'manual' => 'bool',
            'jobPartReference' => '',
            'posted' => 'bool',
            'taxBase' => '',
            'commBase' => '',
            'adjustedTotal' => '',
        ];
    }
}