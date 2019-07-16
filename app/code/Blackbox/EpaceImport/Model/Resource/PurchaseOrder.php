<?php

/**
 * Flat purchase order resource
 */
class Blackbox_EpaceImport_Model_Resource_PurchaseOrder extends Mage_Sales_Model_Resource_Abstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix                  = 'epacei_purchase_order_resource';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject                  = 'resource';

    /**
     * Model Initialization
     *
     */
    protected function _construct()
    {
        $this->_init('epacei/purchase_order', 'entity_id');
    }
}
