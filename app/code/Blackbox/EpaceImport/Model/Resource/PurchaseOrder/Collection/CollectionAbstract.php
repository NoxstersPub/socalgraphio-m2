<?php

namespace Blackbox\EpaceImport\Model\Resource\PurchaseOrder\Collection;

abstract class CollectionAbstract extends \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * PurchaseOrder object
     *
     * @var Blackbox_EpaceImport_Model_PurchaseOrder
     */
    protected $_purchaseOrder   = null;

    /**
     * PurchaseOrder field for setPurchaseOrderFilter
     *
     * @var string
     */
    protected $_purchaseOrderField   = 'parent_id';

    /**
     * Set sales purchase order model as parent collection object
     *
     * @param Blackbox_EpaceImport_Model_PurchaseOrder $purchaseOrder
     * @return Blackbox_EpaceImport_Model_Resource_PurchaseOrder_Collection_Abstract
     */
    public function setPurchaseOrder($purchaseOrder)
    {
        $this->_purchaseOrder = $purchaseOrder;
        if ($this->_eventPrefix && $this->_eventObject) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();	
            /** @var \Magento\Framework\Event\ManagerInterface $manager */
            $manager = $om->get('Magento\Framework\Event\ManagerInterface');
            $manager->dispatch($this->_eventPrefix . '_set_purchase_order', array(
                'collection' => $this,
                $this->_eventObject => $this,
                'purchase_order' => $purchaseOrder
            ));
        }

        return $this;
    }

    /**
     * Retrieve sales purchase order as parent collection object
     *
     * @return Blackbox_EpaceImport_Model_PurchaseOrder|null
     */
    public function getPurchaseOrder()
    {
        return $this->_purchaseOrder;
    }

    /**
     * Add purchase order filter
     *
     * @param int|Blackbox_EpaceImport_Model_PurchaseOrder $purchaseOrder
     * @return Blackbox_EpaceImport_Model_Resource_PurchaseOrder_Collection_Abstract
     */
    public function setPurchaseOrderFilter($purchaseOrder)
    {
        if ($purchaseOrder instanceof \Blackbox\EpaceImport\Model\PurchaseOrder) {
            $this->setPurchaseOrder($purchaseOrder);
            $purchaseOrderId = $purchaseOrder->getId();
            if ($purchaseOrderId) {
                $this->addFieldToFilter($this->_purchaseOrderField, $purchaseOrderId);
            } else {
                $this->_totalRecords = 0;
                $this->_setIsLoaded(true);
            }
        } else {
            $this->addFieldToFilter($this->_purchaseOrderField, $purchaseOrder);
        }
        return $this;
    }
}
