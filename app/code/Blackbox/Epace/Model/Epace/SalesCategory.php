<?php

namespace Blackbox\Epace\Model\Epace;

class SalesCategory extends \Blackbox\Epace\Model\Epace\AbstractObject
{
    protected function _construct()
    {
        $this->_init('SalesCategory', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'name' => 'string',
            'taxable' => 'bool',
            'active' => 'bool',
            'commissionable' => 'bool',
            'taxReport' => 'bool',
            'salesReport' => 'bool',
            'glAccount' => 'int',
            'glDepartment' => 'int',
            'includeInDiscount' => 'bool',
            'commission' => 'bool',
        ];
    }
}