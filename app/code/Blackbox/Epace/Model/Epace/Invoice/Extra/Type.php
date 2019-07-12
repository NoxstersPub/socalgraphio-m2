<?php

namespace Blackbox\Epace\Model\Epace\Invoice\Extra;

class Type extends \Blackbox\Epace\Model\Epace\Invoice\ChildAbstract
{
    const EXTRA_CATEGORY_FREIGHT = 1;
    const EXTRA_CATEGORY_MISC = 2;
    const EXTRA_CATEGORY_TYPE_DEPOSIT = 3;
    const EXTRA_CATEGORY_TYPE_DISCOUNT = 4;
    const EXTRA_CATEGORY_TYPE_POSTAGE = 5;

    protected function _construct()
    {
        $this->_init('InvoiceExtraType', 'id');
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
            'id' => 'int',
            'description' => 'string',
            'salesCategory' => 'int',
            'extraCategory' => 'int',
            'active' => 'bool',
        ];
    }
}