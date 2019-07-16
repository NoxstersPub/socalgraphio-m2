<?php

namespace Blackbox\Epace\Model\Epace\Ship;

class Via extends \Blackbox\Epace\Model\Epace\EpaceObject
{
    protected function _construct()
    {
        $this->_init('ShipVia', 'id');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Ship_Provider|bool
     */
    public function getShipProvider()
    {
        return $this->_getObject('provider', 'provider', 'efi/ship_provider', true);
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Ship_Provider $provider
     * @return $this
     */
    public function setShipProvider(\Blackbox\Epace\Model\Epace\Ship\Provider $provider)
    {
        return $this->_setObject('provider', $provider);
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'provider' => 'int',
            'description' => 'string',
            'minimumWeight' => '',
            'maximumWeight' => '',
            'multiBoxShipping' => 'bool',
            'maxWeightPerBox' => '',
            'active' => 'bool',
            'daysintransit' => '',
            'cutOffTime' => 'date',
            'earliestDeliveryTime' => 'date',
            'dateCalcType' => '',
            'dsfDeliveryMethod' => 'bool',
            'availForRelay' => 'bool',
            'dsfShared' => 'bool',
            'billOfLading' => 'bool',
            'activityCode' => '',
            'availableInEcommerce' => 'bool',
        ];
    }
}