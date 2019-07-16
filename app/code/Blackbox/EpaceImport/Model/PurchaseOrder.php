<?php

namespace Blackbox\EpaceImport\Model;

class PurchaseOrder extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Identifier for history item
     */
    const ENTITY                                = 'purchase_order';

    /**
     * PurchaseOrder statuses
     */
    const STATUS_CLOSED                 = 'C';
    const STATUS_OPEN                   = 'O';
    const STATUS_PENDING                = 'P';
    const STATUS_RECEIVED               = 'R';
    const STATUS_CANCELLED              = 'X';
    const STATUS_RECONCILED             = 'Z';

    /**
     * PurchaseOrder flags
     */
    const ACTION_FLAG_CANCEL                    = 'cancel';
    /*
     * Identifier for history item
     */
    const HISTORY_ENTITY_NAME = 'purchase_order';

    protected $_eventPrefix = 'epacei_purchase_order';
    protected $_eventObject = 'purchase_order';

    protected $_items           = null;
    protected $_statusHistory   = null;

    protected $_relatedObjects  = array();
    protected $_purchaseOrderCurrency   = null;
    protected $_baseCurrency    = null;

    /**
     * Array of action flags for canUnhold, canEdit, etc.
     *
     * @var array
     */
    protected $_actionFlag = array();

    /**
     * Flag: if after purchase order placing we can send new email to the customer.
     *
     * @var bool
     */
    protected $_canSendNewEmailFlag = true;

    /*
     * Identifier for history item
     *
     * @var string
     */
    protected $_historyEntityName = self::HISTORY_ENTITY_NAME;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('Blackbox\EpaceImport\Model\PurchaseOrder');
    }

    /**
     * Clear purchase order object data
     *
     * @param string $key data key
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder
     */
    public function unsetData($key=null)
    {
        parent::unsetData($key);
        if (is_null($key)) {
            $this->_items = null;
        }
        return $this;
    }

    /**
     * Retrieve can flag for action (edit, unhold, etc..)
     *
     * @param string $action
     * @return boolean|null
     */
    public function getActionFlag($action)
    {
        if (isset($this->_actionFlag[$action])) {
            return $this->_actionFlag[$action];
        }
        return null;
    }

    /**
     * Set can flag value for action (edit, unhold, etc...)
     *
     * @param string $action
     * @param boolean $flag
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder
     */
    public function setActionFlag($action, $flag)
    {
        $this->_actionFlag[$action] = (boolean) $flag;
        return $this;
    }

    /**
     * Load purchase order by custom attribute value. Attribute value should be unique
     *
     * @param string $attribute
     * @param string $value
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder
     */
    public function loadByAttribute($attribute, $value)
    {
        $this->load($value, $attribute);
        return $this;
    }

    /**
     * Retrieve store model instance
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        $storeId = $this->getStoreId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        if ($storeId) {
            return $storeManager->getStore($storeId);
        }
        return $storeManager->getStore();
    }

    public function getContactName()
    {
        return $this->getContactFirstname() . ' ' . $this->getContactLastname();
    }

    /**
     * Retrieve purchase order cancel availability
     *
     * @return bool
     */
    public function canCancel()
    {
        $status = $this->getStatus();
        if ($this->isCanceled() || $status === self::STATUS_CANCELLED || $status === self::STATUS_CLOSED || $status === self::STATUS_RECEIVED) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_CANCEL) === false) {
            return false;
        }

        return true;
    }

    /**
     * Check if comment can be added to purchase order history
     *
     * @return bool
     */
    public function canComment()
    {
        return true;
    }

    /**
     * Retrieve purchase order edit availability
     *
     * @return bool
     */
    public function canEdit()
    {
        return false;
    }

    /**
     * Retrieve shipping method
     *
     * @param bool $asObject return carrier code and shipping method data as object
     * @return string|Varien_Object
     */
    public function getShippingMethod($asObject = false)
    {
        $shippingMethod = parent::getShippingMethod();
        if (!$asObject) {
            return $shippingMethod;
        } else {
            $segments = explode('_', $shippingMethod, 2);
            if (!isset($segments[1])) {
                $segments[1] = $segments[0];
            }
            list($carrierCode, $method) = $segments;
            return new Varien_Object(array(
                'carrier_code' => $carrierCode,
                'method'       => $method
            ));
        }
    }

    /**
     * Retrieve purchase order configuration model
     *
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder_Config
     */
    public function getConfig()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $configModel = $objectManager->create('\Blackbox\EpaceImport\Model\PurchaseOrder\Config');
        return $configModel;
    }

    protected function _setStatus($status, $comment = '')
    {
        $this->setData('status', $status);
        $history = $this->addStatusHistoryComment($comment, false); // no sense to set $status again
        return $this;
    }

    /**
     * Retrieve label of purchase order status
     *
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->getConfig()->getStatusLabel($this->getStatus());
    }

    /**
     * Add status change information to history
     * @deprecated after 1.4.0.0-alpha3
     *
     * @param  string $status
     * @param  string $comment
     * @param  bool $isCustomerNotified
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder
     */
    public function addStatusToHistory($status, $comment = '', $isCustomerNotified = false)
    {
        $history = $this->addStatusHistoryComment($comment, $status)
            ->setIsCustomerNotified($isCustomerNotified);
        return $this;
    }

    /*
     * Add a comment to purchase order
     * Different or default status may be specified
     *
     * @param string $comment
     * @param string $status
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder_Status_History
     */
    public function addStatusHistoryComment($comment, $status = false)
    {
        if (false === $status) {
            $status = $this->getStatus();
        } elseif (true === $status) {
            $status = $this->getConfig()->getStateDefaultStatus($this->getState());
        } else {
            $this->setStatus($status);
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $history = $objectManager->create('\Blackbox\EpaceImport\Model\PurchaseOrder\Status\History')
            ->setStatus($status)
            ->setComment($comment)
            ->setEntityName($this->_historyEntityName);
        $this->addStatusHistory($history);
        return $history;
    }

    /**
     * Overrides entity id, which will be saved to comments history status
     *
     * @param string $status
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder
     */
    public function setHistoryEntityName( $entityName )
    {
        $this->_historyEntityName = $entityName;
        return $this;
    }

    /**
     * Cancel purchase order
     *
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder
     */
    public function cancel()
    {
        if ($this->canCancel()) {
            $this->registerCancellation();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();	
            /** @var \Magento\Framework\Event\ManagerInterface $manager */
            $manager = $objectManager->get('Magento\Framework\Event\ManagerInterface');
            $manager->dispatch('purchase_order_cancel_after', array('purchase_order' => $this));
        }

        return $this;
    }

    /**
     * Prepare purchase order totals to cancellation
     * @param string $comment
     * @param bool $graceful
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder
     * @throws Mage_Core_Exception
     */
    public function registerCancellation($comment = '', $graceful = true)
    {
        if ($this->canCancel()) {
            $cancelStatus = self::STATUS_CANCELLED;

            $this->_setStatus($cancelStatus, $comment);
        } elseif (!$graceful) {
            throw new FrameworkException('PurchaseOrder does not allow to be canceled.');
        }
        return $this;
    }

    public function getItemsCollection($filterByTypes = array(), $nonChildrenOnly = false)
    {
        if (is_null($this->_items)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resourceModel = $objectManager->create('\Blackbox\EpaceImport\Model\Resource\PurchaseOrder\Item\Collection');
            $this->_items = $resourceModel->setPurchaseOrderFilter($this);

            if ($this->getId()) {
                foreach ($this->_items as $item) {
                    $item->setPurchaseOrder($this);
                }
            }
        }
        return $this->_items;
    }

    /**
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder_Item[]
     */
    public function getAllItems()
    {
        $items = array();
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted()) {
                $items[] =  $item;
            }
        }
        return $items;
    }

    public function getItemById($itemId)
    {
        return $this->getItemsCollection()->getItemById($itemId);
    }

    public function addItem(\Blackbox\EpaceImport\Model\PurchaseOrder\Item $item)
    {
        $item->setPurchaseOrder($this);
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }

/*********************** STATUSES ***************************/

    /**
     * Enter description here...
     *
     * @return \Blackbox\EpaceImport\Model\Resource_PurchaseOrder_Status_History_Collection
     */
    public function getStatusHistoryCollection($reload=false)
    {
        if (is_null($this->_statusHistory) || $reload) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resourceModel = $objectManager->create('\Blackbox\EpaceImport\Model\Resource\PurchaseOrder\Status\History\Collection');
            $this->_statusHistory = $resourceModel->setPurchaseOrderFilter($this)
                                        ->setPurchaseOrder('created_at', 'desc')
                                        ->setPurchaseOrder('entity_id', 'desc');

            if ($this->getId()) {
                foreach ($this->_statusHistory as $status) {
                    $status->setPurchaseOrder($this);
                }
            }
        }
        return $this->_statusHistory;
    }

    /**
     * Return collection of purchase order status history items.
     *
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder_Status_History[]
     */
    public function getAllStatusHistory()
    {
        $history = array();
        foreach ($this->getStatusHistoryCollection() as $status) {
            if (!$status->isDeleted()) {
                $history[] =  $status;
            }
        }
        return $history;
    }

    /**
     * Return collection of visible on frontend purchase order status history items.
     *
     * @return array
     */
    public function getVisibleStatusHistory()
    {
        $history = array();
        foreach ($this->getStatusHistoryCollection() as $status) {
            if (!$status->isDeleted() && $status->getComment() && $status->getIsVisibleOnFront()) {
                $history[] =  $status;
            }
        }
        return $history;
    }

    public function getStatusHistoryById($statusId)
    {
        foreach ($this->getStatusHistoryCollection() as $status) {
            if ($status->getId()==$statusId) {
                return $status;
            }
        }
        return false;
    }

    /**
     * Set the purchase order status history object and the purchase order object to each other
     * Adds the object to the status history collection, which is automatically saved when the purchase order is saved.
     * See the entity_id attribute backend model.
     * Or the history record can be saved standalone after this.
     *
     * @param \Blackbox\EpaceImport\Model\PurchaseOrder_Status_History $status
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder
     */
    public function addStatusHistory(\Blackbox\EpaceImport\Model\PurchaseOrder\Status\History $history)
    {
        $history->setPurchaseOrder($this);
        $this->setStatus($history->getStatus());
        if (!$history->getId()) {
            $this->getStatusHistoryCollection()->addItem($history);
        }
        return $this;
    }


    /**
     * Enter description here...
     *
     * @return string
     */
    public function getRealPurchaseOrderId()
    {
        return $this->getPoNumber();
    }

    /**
     * Get currency model instance. Will be used currency with which purchase order placed
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getPurchaseOrderCurrency()
    {
        if (is_null($this->_purchaseOrderCurrency)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $currency = $objectManager->create('Magento\Directory\Model\Currency');
            $this->_purchaseOrderCurrency = $currency->load($this->getOrderCurrencyCode());
        }
        return $this->_purchaseOrderCurrency;
    }

    /**
     * Get formated price value including purchase order currency rate to purchase order website currency
     *
     * @param   float $price
     * @param   bool  $addBrackets
     * @return  string
     */
    public function formatPrice($price, $addBrackets = false)
    {
        return $this->formatPricePrecision($price, 2, $addBrackets);
    }

    public function formatPricePrecision($price, $precision, $addBrackets = false)
    {
        return $this->getPurchaseOrderCurrency()->formatPrecision($price, $precision, array(), true, $addBrackets);
    }

    /**
     * Retrieve text formated price value includeing purchase order rate
     *
     * @param   float $price
     * @return  string
     */
    public function formatPriceTxt($price)
    {
        return $this->getPurchaseOrderCurrency()->formatTxt($price);
    }

    /**
     * Retrieve purchase order website currency for working with base prices
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getBaseCurrency()
    {
        if (is_null($this->_baseCurrency)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $currency = $objectManager->create('Magento\Directory\Model\Currency');
            $this->_baseCurrency = $currency->load($this->getBaseCurrencyCode());
        }
        return $this->_baseCurrency;
    }

    public function formatBasePrice($price)
    {
        return $this->formatBasePricePrecision($price, 2);
    }

    public function formatBasePricePrecision($price, $precision)
    {
        return $this->getBaseCurrency()->formatPrecision($price, $precision);
    }

    public function isCurrencyDifferent()
    {
        return $this->getOrderCurrencyCode() != $this->getBaseCurrencyCode();
    }

    /**
     * Get formated purchase order created date in store timezone
     *
     * @param   string $format date format type (short|medium|long|full)
     * @return  string
     */
    public function getCreatedAtFormated($format)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $timeZone = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        return $timeZone->formatDate($this->getCreatedAtStoreDate(), $format, true);
    }

    /**
     * @return \Blackbox\EpaceImport\Model\Address
     */
    public function getShipToAddress()
    {
        if (!$this->getData('ship_to_address') && $this->getData('ship_to_address_id')) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $modelAddress = $objectManager->create('\Blackbox\EpaceImport\Model\Address');
            $address = $modelAddress->load($this->getData('ship_to_address_id'));
            if ($address->getId()) {
                $this->setData('ship_to_address', $address);
            }
        }
        return $this->getData('ship_to_address');
    }

    /**
     * @return \Blackbox\EpaceImport\Model\Address
     */
    public function getVendorAddress()
    {
        if (!$this->getData('vendor_address') && $this->getData('vendor_address_id')) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $modelAddress = $objectManager->create('\Blackbox\EpaceImport\Model\Address');
            $address = $modelAddress->load($this->getData('vendor_address_id'));
            if ($address->getId()) {
                $this->setData('vendor_address', $address);
            }
        }
        return $this->getData('vendor_address');
    }

    public function setShipToAddress($address)
    {
        $this->setShipToAddressId($address->getId())
            ->setData('ship_to_address', $address);
        return $this;
    }

    public function setVendorAddress($address)
    {
        $this->setVendorAddressId($address->getId())
            ->setData('vendor_address', $address);
        return $this;
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if (!$this->getId()) {
            $store = $this->getStore();
            $name = array($store->getWebsite()->getName(),$store->getGroup()->getName(),$store->getName());
            $this->setStoreName(implode("\n", $name));
        }

        /**
         * Process items dependency for new purchase order
         */
        if (!$this->getId()) {
            $itemsCount = 0;
            foreach ($this->getAllItems() as $item) {
                $parent = $item->getQuoteParentItemId();
                if ($parent && !$item->getParentItem()) {
                    $item->setParentItem($this->getItemByQuoteItemId($parent));
                } elseif (!$parent) {
                    $itemsCount++;
                }
            }
            // Set items count
            $this->setTotalItemCount($itemsCount);
        }

        if ($address = $this->getData('ship_to_address')) {
            if (!$address->getId()) {
                $address->save();
            }
            $this->setShipToAddressId($address->getId());
        } else {
            if ($this->hasShipToAddressId() && $this->getShipToAddressId() === null) {
                $this->unsShipToAddressId();
            }
        }

        if ($address = $this->getData('vendor_address')) {
            if (!$address->getId()) {
                $address->save();
            }
            $this->setVendorAddressId($address->getId());
        } else {
            if ($this->hasVendorAddressId() && $this->getVendorAddressId() === null) {
                $this->unsVendorAddressId();
            }
        }

        $this->setData('protect_code', substr(md5(uniqid(mt_rand(), true) . ':' . microtime(true)), 5, 6));
        return $this;
    }

    /**
     * Save purchase order related objects
     *
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder
     */
    protected function _afterSave()
    {
        if (null !== $this->_items) {
            $this->_items->save();
        }
        if (null !== $this->_statusHistory) {
            $this->_statusHistory->save();
        }
        foreach ($this->getRelatedObjects() as $object) {
            $object->save();
        }
        return parent::_afterSave();
    }

    public function getStoreGroupName()
    {
        $storeId = $this->getStoreId();
        if (is_null($storeId)) {
            return $this->getStoreName(1); // 0 - website name, 1 - store group name, 2 - store name
        }
        return $this->getStore()->getGroup()->getName();
    }

    /**
     * Resets all data in object
     * so after another load it will be complete new object
     *
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder
     */
    public function reset()
    {
        $this->unsetData();
        $this->_actionFlag = array();
        $this->_items = null;
        $this->_statusHistory = null;
        $this->_relatedObjects = array();
        $this->_purchaseOrderCurrency = null;
        $this->_baseCurrency = null;

        return $this;
    }

    /**
     * Check whether purchase order is canceled
     *
     * @return bool
     */
    public function isCanceled()
    {
        return ($this->getStatus() === self::STATUS_CANCELLED);
    }

    /**
     * Protect purchase order delete from not admin scope
     * @return \Blackbox\EpaceImport\Model\PurchaseOrder
     */
    protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        return parent::_beforeDelete();
    }

    /**
     * Processing object after save data
     * Updates relevant grid table records.
     *
     * @return Mage_Core_Model_Abstract
     */
    public function afterCommitCallback()
    {
        return \Magento\Framework\Model\AbstractModel::afterCommitCallback();
    }
}
