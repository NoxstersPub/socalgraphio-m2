<?php

namespace Blackbox\EpaceImport\Model;

class Address extends \Magento\Customer\Model\Address\AbstractAddress
{
    protected $_purchaseOrder;
    
    protected $_eventPrefix = 'epacei_address';
    protected $_eventObject = 'address';
    protected $objectManager;
    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_init('Blackbox\EpaceImport\Model\address');
    }

    /**
     * Set order
     *
     * @return $this
     */
    public function setPurchaseOrder(Blackbox\EpaceImport\Model\PurchaseOrder $order)
    {
        $this->_purchaseOrder = $order;
        return $this;
    }

    /**
     * Get order
     *
     * @return Blackbox\EpaceImport\Model_PurchaseOrder
     */
    public function getPurchaseOrder()
    {
        if (!$this->_purchaseOrder) {
            $this->_purchaseOrder = $this->objectManager->create('Blackbox\EpaceImport\Model\PurchaseOrder')->load($this->getParentId());
        }
        return $this->_purchaseOrder;
    }

    /**
     * Before object save manipulations
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getParentId() && $this->getPurchaseOrder()) {
            $this->setParentId($this->getPurchaseOrder()->getId());
        }

        // Init customer address id if customer address is assigned
        if ($this->getCustomerAddress()) {
            $this->setCustomerAddressId($this->getCustomerAddress()->getId());
        }

        return $this;
    }
}
