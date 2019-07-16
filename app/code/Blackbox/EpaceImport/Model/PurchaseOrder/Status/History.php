<?php

namespace Blackbox\EpaceImport\Model\PurchaseOrder\Status;

class History extends \Magento\Sales\Model\AbstractModel
{
    const CUSTOMER_NOTIFICATION_NOT_APPLICABLE = 2;

    /**
     * PurchaseOrder instance
     *
     * @var \Blackbox\EpaceImport\Model\PurchaseOrder
     */
    protected $_purchaseOrder;

    /**
     * Whether setting order again is required (for example when setting non-saved yet order)
     * @deprecated after 1.4, wrong logic of setting order id
     * @var bool
     */
    private $_shouldSetPurchaseOrderBeforeSave = false;

    protected $_eventPrefix = 'sales_purchase_order_status_history';
    protected $_eventObject = 'status_history';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('Blackbox\EpaceImport\Model\PurchaseOrder\Status\History');
    }

    /**
     * Set order object and grab some metadata from it
     *
     * @param   \Blackbox\EpaceImport\Model\PurchaseOrder $order
     * @return  \Blackbox\EpaceImport\Model\PurchaseOrder_Status_History
     */
    public function setPurchaseOrder(\Blackbox\EpaceImport\Model\PurchaseOrder $order)
    {
        $this->_purchaseOrder = $order;
        $this->setStoreId($order->getStoreId());
        return $this;
    }

    /**
     * Notification flag
     *
     * @param  mixed $flag OPTIONAL (notification is not applicable by default)
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder_Status_History
     */
    public function setIsCustomerNotified($flag = null)
    {
        if (is_null($flag)) {
            $flag = self::CUSTOMER_NOTIFICATION_NOT_APPLICABLE;
        }

        return $this->setData('is_customer_notified', $flag);
    }

    /**
     * Customer Notification Applicable check method
     *
     * @return boolean
     */
    public function isCustomerNotificationNotApplicable()
    {
        return $this->getIsCustomerNotified() == self::CUSTOMER_NOTIFICATION_NOT_APPLICABLE;
    }

    /**
     * Retrieve order instance
     *
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder
     */
    public function getPurchaseOrder()
    {
        return $this->_purchaseOrder;
    }

    /**
     * Retrieve status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        if($this->getPurchaseOrder()) {
            return $this->getPurchaseOrder()->getConfig()->getStatusLabel($this->getStatus());
        }
    }

    /**
     * Get store object
     *
     * @return unknown
     */
    public function getStore()
    {
        if ($this->getPurchaseOrder()) {
            return $this->getPurchaseOrder()->getStore();
        }
        return Mage::app()->getStore();
    }

    /**
     * Set order again if required
     *
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder_Status_History
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getParentId() && $this->getPurchaseOrder()) {
            $this->setParentId($this->getPurchaseOrder()->getId());
        }

        return $this;
    }
}
