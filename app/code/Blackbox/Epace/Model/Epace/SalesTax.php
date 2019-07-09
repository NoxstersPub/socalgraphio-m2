<?php
namespace Blackbox\Epace\Model\Epace;

class SalesTax extends \Blackbox\Epace\Model\Epace\AbstractObject
{
    protected function _construct()
    {
        $this->_init('SalesTax', 'id');
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

    public function getDefinition()
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'taxableLimit' => 'float',
            'salesCategory' => 'int',
            'rate1' => 'float',
            'rate2' => 'float',
            'rate3' => 'float',
            'rate4' => 'float',
            'rate5' => 'float',
            'rate6' => 'float',
            'active' => 'bool',
            'actualCostBasedTaxing' => 'bool',
            'calculateCanadianSalesTax' => 'bool',
            'selfTax' => 'bool',
            'taxRate' => 'float',
        ];
    }
}