<?php

namespace Blackbox\Epace\Model\Epace\Receivable;

class Line extends \Blackbox\Epace\Model\Epace\EpaceObject
{
    const ENTRY_TYPE_SALES_DISTRIBUTION = 11;
    const ENTRY_TYPE_TAX_DISTRIBUTION = 7;
    const ENTRY_TYPE_COMISSION_DISTRIBUTION = 33;

    protected function _construct()
    {
        $this->_init('ReceivableLine', 'id');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Receivable|false
     */
    public function getReceivable()
    {
        return $this->_getObject('receivable', 'receivable', 'efi/receivable');
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
     * @return \Blackbox\Epace\Model\Epace\SalesPerson|false
     */
    public function getSalesPerson()
    {
        return $this->_getObject('salesPerson', 'salesPerson', 'efi/salesPerson');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\SalesPerson $salesPerson
     * @return $this
     */
    public function setSalesPerson(\Blackbox\Epace\Model\Epace\SalesPerson $salesPerson)
    {
        return $this->_setObject('salesPerson', $salesPerson);
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'receivable' => 'int',
            'entryType' => 'int',
            'salesCategory' => 'int',
            'amount' => 'float',
            'salesTax' => 'string',
            'taxBase' => 'float',
            'salesPerson' => 'int',
            'comissionBase' => 'float'
        ];
    }
}