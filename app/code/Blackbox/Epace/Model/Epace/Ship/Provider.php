<?php

namespace Blackbox\Epace\Model\Epace\Ship;

class Provider extends \Blackbox\Epace\Model\Epace\EpaceObject
{
    protected function _construct()
    {
        $this->_init('ShipProvider', 'id');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Ship_Via[]
     */
    public function getShipVias()
    {
        return $this->_getChildItems('efi/ship_via_collection', [
            'provider' => $this->getId()
        ], function ($item) {
            $item->setShipProvider($this);
        });
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'name' => 'string',
            'trackingUrl' => 'string',
            'active' => 'bool',
        ];
    }
}