<?php

namespace Blackbox\Epace\Model\Epace;

class SalesPerson extends \Blackbox\Epace\Model\Epace\EpaceObject
{
    public function _construct()
    {
        $this->_init('SalesPerson', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'name' => '',
            'email' => '',
            'annualQuota' => '',
            'active' => 'bool',
            'commissionRate' => '',
        ];
    }
}
