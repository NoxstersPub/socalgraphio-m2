<?php

namespace Blackbox\Epace\Model\Epace\Invoice;

class CommDist extends \Blackbox\Epace\Model\Epace\Invoice\ChildAbstract
{
    protected function _construct()
    {
        $this->_init('InvoiceCommDist', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'invoice' => 'int',
            'amount' => '',
            'commBase' => '',
            'memoCreated' => 'bool',
            'salesPerson' => 'int',
            'lockCommBase' => 'bool',
            'lockAmount' => 'bool',
            'salesCategory' => '',
            'commissionRate' => '',
            'manual' => 'bool',
            'posted' => 'bool',
            'amountAdjustment' => '',
            'adjustedTotal' => '',
        ];
    }
}