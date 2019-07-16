<?php
namespace Blackbox\Epace\Model\Epace;

use \Blackbox\Epace\Model\Epace\PersonsTrait;

class POStatus extends \Blackbox\Epace\Model\Epace\EpaceObject 
{
    protected function _construct()
    {
        $this->_init('POStatus', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'string',
            'description' => 'string'
        ];
    }
}