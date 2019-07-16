<?php

namespace Blackbox\Epace\Model\Epace;

class Carton extends \Blackbox\Epace\Model\Epace\Shipment\ChildAbstract
{
    protected function _construct()
    {
        $this->_init('Carton', 'id');
    }

    /**
     * @return Blackbox_Epace_Model_Epace_Skid|bool
     */
    public function getSkid()
    {
        return $this->_getObject('skid', 'skid', 'efi/skid');
    }

    /**
     * @param Blackbox_Epace_Model_Epace_Skid $skid
     * @return $this
     */
    public function setSkid(\Blackbox\Epace\Model\Epace\Skid $skid)
    {
        return $this->_setObject('skid', $skid);
    }

    /**
     * @return Blackbox_Epace_Model_Epace_Carton_Content[]
     */
    public function getContents()
    {
        return $this->_getChildItems('efi/carton_content_collection', [
            'carton' => $this->getId()
        ], function ($item) {
            $item->setCarton($this);
        });
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'shipment' => 'int',
            'count' => '',
            'quantity' => '',
            'addDefaultContent' => 'bool',
            'note' => '',
            'trackingNumber' => '',
            'actualDate' => 'date',
            'actualTime' => 'date',
            'skidCount' => 'int',
            'skid' => 'int',
            'weight' => 'float',
            'cost' => '',
            'trackingLink' => '',
            'totalQuantity' => '',
            'totalSkidQuantity' => '',
        ];
    }

    protected function getShipmentKey()
    {
        return 'shipment';
    }
}