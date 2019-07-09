<?php

namespace Blackbox\Epace\Model\Epace\Invoice;

class Extra extends \Blackbox\Epace\Model\Epace\Invoice\ChildAbstract
{
    protected function _construct()
    {
        $this->_init('InvoiceExtra', 'id');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Invoice_Extra_Type
     */
    public function getType()
    {
        return $this->_getObject('type', 'invoiceExtraType', 'efi/invoice_extra_type');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Invoice_Extra_Type $type
     * @return $this
     */
    public function setType(\Blackbox\Epace\Model\Epace\Invoice\Extra_Type $type)
    {
        return $this->_setObject('type', $type);
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'lineNum' => 'int',
            'invoice' => 'int',
            'price' => 'float',
            'memoCreated' => 'bool',
            'invoiceExtraType' => 'int',
            'manual' => 'bool',
            'jobPartReference' => 'string',
            'posted' => 'bool',
            'adjustedTotal' => 'float',
        ];
    }
}