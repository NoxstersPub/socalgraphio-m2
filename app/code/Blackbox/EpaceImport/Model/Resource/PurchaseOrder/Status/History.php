<?php

/**
 * Flat sales order status history resource
 *
 * @category    Mage
 * @package     Blackbox_EpaceImport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Blackbox_EpaceImport_Model_Resource_PurchaseOrder_Status_History extends Blackbox_EpaceImport_Model_Resource_PurchaseOrder_Abstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'epace_purchase_order_status_history_resource';

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('epacei/purchase_order_status_history', 'entity_id');
    }
}
