<?php

/**
 * Flat sales order item resource
 *
 * @category    Mage
 * @package     Blackbox_EpaceImport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Blackbox_EpaceImport_Model_Resource_PurchaseOrder_Item extends Blackbox_EpaceImport_Model_Resource_PurchaseOrder_Abstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'epacei_purchase_order_item_resource';

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('epacei/purchase_order_item', 'item_id');
    }
}
