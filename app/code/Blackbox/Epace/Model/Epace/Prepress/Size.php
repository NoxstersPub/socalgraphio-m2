<?php

namespace Blackbox\Epace\Model\Epace\Prepress;

class Size extends \Blackbox\Epace\Model\Epace\EpaceObject
{
    protected function _construct()
    {
        $this->_init('PrepressSize', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'prepressItem' => 'float',
            'sizeWidth' => 'float',
            'sizeHeight' => 'float',
        ];
    }
}