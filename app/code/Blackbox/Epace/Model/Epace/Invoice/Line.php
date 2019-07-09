<?php

namespace Blackbox\Epace\Model\Epace\Invoice;

class Line extends \Blackbox\Epace\Model\Epace\Invoice\ChildAbstract
{
    const LINE_TYPE_PRICE = 1;
    const LINE_TYPE_QUOTE_ITEM = 2;
    const LINE_TYPE_DESCRIPTION = 4;

    protected function _construct()
    {
        $this->_init('InvoiceLine', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'invoice' => 'int',
            'lineNum' => '',
            'qtyInvoiced' => '',
            'qtyOrdered' => '',
            'qtyShipped' => '',
            'unitPrice' => '',
            'totalPrice' => '',
            'memoCreated' => 'bool',
            'lineType' => '',
            'salesCategory' => '',
            'uom' => '',
            'description' => '',
            'flatPrice' => 'bool',
            'jobPartReference' => '',
            'posted' => 'bool',
            'adjustedTotal' => '',
        ];
    }
}