<?php

namespace Blackbox\Epace\Model\Epace\Job;

class Component extends \Blackbox\Epace\Model\Epace\Job\Part\AbstractChild
{
    protected function _construct()
    {
        $this->_init('JobComponent', 'id');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Ship_Via|bool
     */
    public function getShipVia()
    {
        return $this->_getObject('shipVia', 'shipVia', 'efi/ship_via', true);
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Ship_Via $shipVia
     * @return $this
     */
    public function setShipVia(\Blackbox\Epace\Model\Epace\Ship\Via $shipVia)
    {
        return $this->_setObject('shipVia', $shipVia);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Contact|bool
     */
    public function getShipToContact()
    {
        return $this->_getObject('contact', 'shipToContact', 'efi/contact');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Contact $contact
     * @return $this
     */
    public function setShipToContact(\Blackbox\Epace\Model\Epace\Contact $contact)
    {
        return $this->_setObject('contact', $contact);
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'job' => 'string',
            'jobPart' => 'string',
            'description' => 'string',
            'finalSizeH' => 'float',
            'finalSizeW' => 'float',
            'shipVia' => 'int',
            'terms' => 'int',
            'productionStatus' => 'string',
            'qtyOrdered' => 'int',
            'qtyToMfg' => 'int',
            'shipToContact' => 'int',
            'colorsS1' => 'int',
            'colorsS2' => 'int',
            'colorsTotal' => 'int',
            'numSigs' => 'int',
            'active' => 'bool',
            'quantityRemaining' => 'float',
            'jobPartKey' => 'string',
        ];
    }

    public function getJobPartKeyField()
    {
        return 'jobPartKey';
    }
}