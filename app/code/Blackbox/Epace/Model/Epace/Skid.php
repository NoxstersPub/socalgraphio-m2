<?php
namespace Blackbox\Epace\Model\Epace;

class Skid extends \Blackbox\Epace\Model\Epace\Shipment\ChildAbstract
{
    protected function _construct()
    {
        $this->_init('Skid', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'jobShipment' => 'int',
            'count' => 'int',
        ];
    }

    protected function getShipmentKey()
    {
        return 'jobShipment';
    }
}