<?php

/**
 * Flat sales order status history collection
 *
 * @category    Mage
 * @package     Blackbox_EpaceImport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Blackbox_EpaceImport_Model_Resource_PurchaseOrder_Status_History_Collection
    extends Blackbox_EpaceImport_Model_Resource_PurchaseOrder_Collection_Abstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'epacei_purchase_order_status_history_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject    = 'purchase_order_status_history_collection';

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('epacei/purchaseOrder_status_history');
    }

    /**
     * Get history object collection for specified instance (order, shipment, invoice or credit memo)
     * Parameter instance may be one of the following types: Blackbox_EpaceImport_Model_PurchaseOrder,
     * Blackbox_EpaceImport_Model_PurchaseOrder_Creditmemo, Blackbox_EpaceImport_Model_PurchaseOrder_Invoice, Blackbox_EpaceImport_Model_PurchaseOrder_Shipment
     *
     * @param mixed $instance
     * @param string $historyEntityName
     *
     * @return Blackbox_EpaceImport_Model_PurchaseOrder_Status_History|null
     */
    public function getUnnotifiedForInstance($instance, $historyEntityName=Blackbox_EpaceImport_Model_PurchaseOrder::HISTORY_ENTITY_NAME)
    {
        if(!$instance instanceof Blackbox_EpaceImport_Model_PurchaseOrder) {
            $instance = $instance->getOrder();
        }
        $this->setPurchaseOrder($instance)->setOrder('created_at', 'desc')
            ->addFieldToFilter('entity_name', $historyEntityName)
            ->addFieldToFilter('is_customer_notified', 0)->setPageSize(1);
        foreach($this as $historyItem) {
            return $historyItem;
        }
        return null;
    }

}
