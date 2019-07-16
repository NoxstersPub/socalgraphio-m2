<?php

namespace Blackbox\Epace\Model\Epace\Purchase_Order;

class Type extends \Blackbox\Epace\Model\Epace\EpaceObject
{
    protected function _construct()
    {
        $this->_init('PurchaseOrderType', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'description' => 'string',
            'poNumberPrefix' => 'int',
            'poNumberSequence' => 'int',
            'autoNumberOnly' => 'bool',
            'active' => 'bool',
            'useManufacturingLocationPrefix' => 'bool',
        ];
    }
}