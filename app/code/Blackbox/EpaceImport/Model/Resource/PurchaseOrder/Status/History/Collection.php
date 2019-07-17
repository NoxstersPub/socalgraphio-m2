<?php
namespace Blackbox\EpaceImport\Model\Resource\PurchaseOrder\Status\History;

/**
 * Flat sales order status history collection
 *
 * @category    Mage
 * @package     \Blackbox\EpaceImport
 * @author      Magento Framework Team <core@magentocommerce.com>
 */
class Collection extends \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\Collection\PurchaseOrderAbstract
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
     * Parameter instance may be one of the following types: \Blackbox\EpaceImport\Model\PurchaseOrder,
     * \Blackbox\EpaceImport\Model\PurchaseOrder\Creditmemo, \Blackbox\EpaceImport\Model\PurchaseOrder\Invoice, \Blackbox\EpaceImport\Model\PurchaseOrder\Shipment
     *
     * @param mixed $instance
     * @param string $historyEntityName
     *
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder\Status\History|null
     */
    public function getUnnotifiedForInstance($instance, $historyEntityName = \Blackbox\EpaceImport\Model\PurchaseOrder::HISTORY_ENTITY_NAME)
    {
        if(!$instance instanceof \Blackbox\EpaceImport\Model\PurchaseOrder) {
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
