<?php

namespace Blackbox\Epace\Model\Epace;

class Salutation extends \Blackbox\Epace\Model\Epace\AbstractObject
{
    protected function _construct()
    {
        $this->_init('Salutation', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'salutation' => 'string'
        ];
    }
}