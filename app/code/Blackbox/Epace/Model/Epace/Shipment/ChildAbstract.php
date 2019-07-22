<?php

namespace Blackbox\Epace\Model\Epace\Shipment;

abstract class ChildAbstract extends \Blackbox\Epace\Model\Epace\EpaceObject
{
    /**
     * @return int
     */
    public function getJobShipmentId()
    {
        return $this->getData($this->getShipmentKey());
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Shipment|bool
     */
    public function getJobShipment()
    {
        return $this->_getObject('jobShipment', $this->getShipmentKey(), 'efi/job_shipment');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Job_Shipment $shipment
     * @return $this
     */
    public function setJobShipment(\Blackbox\Epace\Model\Epace\Job\Shipment $shipment)
    {
        return $this->_setObject('jobShipment', $shipment);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Shipment|bool
     * @deprecated
     */
    public function getShipment()
    {
        return $this->getJobShipment();
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Job_Shipment $shipment
     * @return $this
     * @deprecated
     */
    public function setShipment(\Blackbox\Epace\Model\Epace\Job\Shipment $shipment)
    {
        return $this->setJobShipment($shipment);
    }

    protected abstract function getShipmentKey();
}