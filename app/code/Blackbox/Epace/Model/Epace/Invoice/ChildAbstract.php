<?php

namespace Blackbox\Epace\Model\Epace\Invoice;

abstract class ChildAbstract extends \Blackbox\Epace\Model\Epace\EpaceObject
{
    /**
     * @return \Blackbox\Epace\Model\Epace\Invoice|bool
     */
    public function getInvoice()
    {
        return $this->_getObject('invoice', 'invoice', 'efi/invoice');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Invoice $invoice
     * @return $this
     */
    public function setInvoice(\Blackbox\Epace\Model\Epace\Invoice $invoice)
    {
        return $this->_setObject('invoice', $invoice);
    }
}