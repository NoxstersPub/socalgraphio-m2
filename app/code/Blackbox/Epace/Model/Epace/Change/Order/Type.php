<?php

namespace Blackbox\Epace\Model\Epace\Change\Order;

class Type extends \Blackbox\Epace\Model\Epace\EpaceObject
{
    protected function _construct()
    {
        $this->_init('ChangeOrderType', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'description' => 'string',
            'billable' => 'bool',
            'postageAdvance' => 'bool'
        ];
    }
}