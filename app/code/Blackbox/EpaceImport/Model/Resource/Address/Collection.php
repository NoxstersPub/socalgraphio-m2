<?php

/**
 * Flat purchase order payment collection
 */
class Blackbox_EpaceImport_Model_Resource_Address_Collection extends Mage_Sales_Model_Resource_Order_Collection_Abstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'epacei_address_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject    = 'epacei_address_collection';

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('epacei/address');
    }

    /**
     * Redeclare after load method for dispatch event
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        Mage::dispatchEvent($this->_eventPrefix . '_load_after', array(
            $this->_eventObject => $this
        ));

        return $this;
    }
}
