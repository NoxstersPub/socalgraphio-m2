<?php

namespace Blackbox\Epace\Model\Epace\Job;

class Shipment extends \Blackbox\Epace\Model\Epace\Job\AbstractChild
{
    protected function _construct()
    {
        $this->_init('JobShipment', 'id');
    }

    /**
     * @return string
     */
    public function getU_processShipperKeySav()
    {
        return $this->getData('U_processShipperKeySav');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setU_processShipperKeySav($value)
    {
        return $this->setData('U_processShipperKeySav', $value);
    }

    /**
     * @return string
     */
    public function getU_processShipperID()
    {
        return $this->getData('U_processShipperID');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setU_processShipperID($value)
    {
        return $this->setData('U_processShipperID', (string)$value);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\ShipmentType|bool
     */
    public function getType()
    {
        return $this->_getObject('type', 'shipmentType', 'efi/shipmentType', true);
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\ShipmentType $contact
     * @return $this
     */
    public function setType(\Blackbox\Epace\Model\Epace\ShipmentType $type)
    {
        return $this->_setObject('type', $type);
    }

    /**
     * @return int
     */
    public function getContactId()
    {
        return $this->getData('contactNumber');
    }

    /**
     * Ship To
     *
     * @return \Blackbox\Epace\Model\Epace\Contact|bool
     */
    public function getContact()
    {
        return $this->_getObject('contact', 'contactNumber', 'efi/contact');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Contact $contact
     * @return $this
     */
    public function setContact(\Blackbox\Epace\Model\Epace\Contact $contact)
    {
        return $this->_setObject('contact', $contact);
    }

    /**
     * @return int
     */
    public function getJobContactId()
    {
        return $this->getData('jobContact');
    }

    /**
     * Ship From
     *
     * @return \Blackbox\Epace\Model\Epace\Job_Contact|bool
     */
    public function getJobContact()
    {
        return $this->_getObject('jobContact', 'jobContact', 'efi/job_contact');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Job_Contact $contact
     * @return $this
     */
    public function setJobContact(\Blackbox\Epace\Model\Epace\Contact $jobContact)
    {
        return $this->_setObject('jobContact', $jobContact);
    }

    /**
     * @return int
     */
    public function getShipBillToContactId()
    {
        return $this->getData('shipBillToContact');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Contact
     */
    public function getShipBillToContact()
    {
        return $this->_getObject('shipBillToContact', 'shipBillToContact', 'efi/contact');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Contact $contact
     * @return $this
     */
    public function setShipBillToContact(\Blackbox\Epace\Model\Epace\Contact $contact)
    {
        return $this->_setObject('shipBillToContact', $contact);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Contact|bool
     */
    public function getShipTo()
    {
        return $this->getContact();
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Contact|bool
     */
    public function getShipFrom()
    {
        return $this->getJobContact();
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Ship_Via|bool
     */
    public function getShipVia()
    {
        return $this->_getObject('shipVia', 'shipVia', 'efi/ship_via');
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
     * @return \Blackbox\Epace\Model\Epace\Carton[]
     */
    public function getCartons()
    {
        return $this->_getChildItems('efi/carton_collection', [
            'shipment' => $this->getId()
        ], function ($item) {
            $item->setShipment($this);
        });
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Skid[]
     */
    public function getSkids()
    {
        return $this->_getChildItems('efi/skid_collection', [
            'jobShipment' => $this->getId()
        ], function ($item) {
            $item->setShipment($this);
        });
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'job' => 'string',
            'date' => 'date',
            'dateForced' => 'bool',
            'time' => 'date',
            'timeForced' => 'bool',
            'promiseDate' => 'date',
            'promiseTime' => 'date',
            'shipmentType' => '',
            'quotedPrice' => '',
            'shipperName' => '',
            'packageDrop' => 'bool',
            'name' => '',
            'address1' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'country' => '',
            'email' => '',
            'contactFirstName' => '',
            'contactLastName' => '',
            'shipInNameOf' => '',
            'contactNumber' => 'int',
            'shipVia' => 'int',
            'saturday' => 'bool',
            'shipToInventory' => 'bool',
            'planned' => 'bool',
            'useLegacyPrintFlowFormat' => 'bool',
            'fromEservice' => 'bool',
            'charges' => '',
            'plannedQuantity' => '',
            'accountNumber' => 'string',
            'jobContact' => 'int',
            'shipBillToContact' => 'int',
            'dsfShippingDetailID' => '',
            'manufacturingLocation' => '',
            'codCompanyCheckAcceptable' => 'bool',
            'freightLinkIntegrated' => 'bool',
            'dsfProductID' => '',
            'shipped' => 'bool',
            'proof' => 'bool',
            'autoCreated' => 'bool',
            'collapsedInUi' => 'bool',
            'sentToDSF' => 'bool',
            'costDistribution' => '',
            'packageDropType' => '',
            'quantityRemaining' => '',
            'quantityStillToReceive' => '',
            'quantity' => '',
            'newItem' => 'bool',
            'billOfLadingAdd' => 'bool',
            'scheduled' => 'bool',
            'stateKey' => '',
        ];
    }
}