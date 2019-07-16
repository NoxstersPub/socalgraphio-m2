<?php

namespace Blackbox\EpaceImport\Model;

class Estimate extends \Magento\Framework\Model\AbstractModel {

    /**
     * Identifier for history item
     */
    const ENTITY = 'estimate';

    /**
     * Estimate statuses
     */
    const STATUS_OPEN = 1;
    const STATUS_CONVERTED_TO_JOB = 2;
    const STATUS_CUSTOMER_SUBMITTED = 3;
    const STATUS_NEED_INFO = 4;
    const STATUS_PRICE_COMPLETE = 5;
    const STATUS_CANCELLED = 6;
    const STATUS_RE_QUOTE = 7;

    /**
     * Estimate flags
     */
    const ACTION_FLAG_CANCEL = 'cancel';
    /*
     * Identifier for history item
     */
    const HISTORY_ENTITY_NAME = 'estimate';

    protected $_eventPrefix = 'epacei_estimate';
    protected $_eventObject = 'estimate';
    protected $_items = null;
    protected $_statusHistory = null;
    protected $_relatedObjects = array();
    protected $_estimateCurrency = null;
    protected $_baseCurrency = null;

    /**
     * Array of action flags for canUnhold, canEdit, etc.
     *
     * @var array
     */
    protected $_actionFlag = array();

    /**
     * Flag: if after estimate placing we can send new email to the customer.
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
    protected function _construct() {
        $this->_init('Blackbox\EpaceImport\Model\Estimate');
    }

    /**
     * Clear estimate object data
     *
     * @param string $key data key
     * @return \Blackbox\EpaceImport\Model\Estimate
     */
    public function unsetData($key = null) {
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
    public function getActionFlag($action) {
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
     * @return \Blackbox\EpaceImport\Model\Estimate
     */
    public function setActionFlag($action, $flag) {
        $this->_actionFlag[$action] = (boolean) $flag;
        return $this;
    }

    /**
     * Load estimate by system increment identifier
     *
     * @param string $incrementId
     * @return \Blackbox\EpaceImport\Model\Estimate
     */
    public function loadByIncrementId($incrementId) {
        return $this->loadByAttribute('increment_id', $incrementId);
    }

    /**
     * Load estimate by custom attribute value. Attribute value should be unique
     *
     * @param string $attribute
     * @param string $value
     * @return \Blackbox\EpaceImport\Model\Estimate
     */
    public function loadByAttribute($attribute, $value) {
        $this->load($value, $attribute);
        return $this;
    }

    /**
     * Retrieve store model instance
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore() {
        $storeId = $this->getStoreId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        if ($storeId) {
            return $storeManager->getStore($storeId);
        }
        return $storeManager->getStore();
    }

    /**
     * Retrieve estimate cancel availability
     *
     * @return bool
     */
    public function canCancel() {
        if (!$this->_canVoidEstimate()) {
            return false;
        }

        $allInvoiced = true;
        foreach ($this->getAllItems() as $item) {
            if ($item->getQtyToInvoice()) {
                $allInvoiced = false;
                break;
            }
        }
        if ($allInvoiced) {
            return false;
        }

        $status = $this->getStatus();
        if ($this->isCanceled() || $status === self::STATUS_CANCELLED) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_CANCEL) === false) {
            return false;
        }

        return true;
    }

    /**
     * Check if comment can be added to estimate history
     *
     * @return bool
     */
    public function canComment() {
        return true;
    }

    /**
     * Retrieve estimate edit availability
     *
     * @return bool
     */
    public function canEdit() {
        return false;
    }

    /**
     * Retrieve estimate configuration model
     *
     * @return \Blackbox\EpaceImport\Model\Estimate_Config
     */
    public function getConfig() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $configModel = $objectManager->create('\Blackbox\EpaceImport\Model\Estimate\Config');
        return $configModel;
    }

    protected function _setStatus($status, $comment = '') {
        $this->setData('status', $status);
        $history = $this->addStatusHistoryComment($comment, false); // no sense to set $status again
        return $this;
    }

    /**
     * Retrieve label of estimate status
     *
     * @return string
     */
    public function getStatusLabel() {
        return $this->getConfig()->getStatusLabel($this->getStatus());
    }

    /**
     * Add status change information to history
     * @deprecated after 1.4.0.0-alpha3
     *
     * @param  string $status
     * @param  string $comment
     * @param  bool $isCustomerNotified
     * @return \Blackbox\EpaceImport\Model\Estimate
     */
    public function addStatusToHistory($status, $comment = '', $isCustomerNotified = false) {
        $history = $this->addStatusHistoryComment($comment, $status)
                ->setIsCustomerNotified($isCustomerNotified);
        return $this;
    }

    /*
     * Add a comment to estimate
     * Different or default status may be specified
     *
     * @param string $comment
     * @param string $status
     * @return \Blackbox\EpaceImport\Model\Estimate_Status_History
     */

    public function addStatusHistoryComment($comment, $status = false) {
        if (false === $status) {
            $status = $this->getStatus();
        } elseif (true === $status) {
            $status = $this->getConfig()->getStateDefaultStatus($this->getState());
        } else {
            $this->setStatus($status);
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $history = $objectManager->create('\Blackbox\EpaceImport\Model\Estimate\Status\History')
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
     * @return \Blackbox\EpaceImport\Model\Estimate
     */
    public function setHistoryEntityName($entityName) {
        $this->_historyEntityName = $entityName;
        return $this;
    }

    /**
     * Cancel estimate
     *
     * @return \Blackbox\EpaceImport\Model\Estimate
     */
    public function cancel() {
        if ($this->canCancel()) {
            $this->registerCancellation();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Magento\Framework\Event\ManagerInterface $manager */
            $manager = $objectManager->get('Magento\Framework\Event\ManagerInterface');
            $manager->dispatch('estimate_cancel_after', array('estimate' => $this));
        }

        return $this;
    }

    /**
     * Prepare estimate totals to cancellation
     * @param string $comment
     * @param bool $graceful
     * @return \Blackbox\EpaceImport\Model\Estimate
     * @throws Mage_Core_Exception
     */
    public function registerCancellation($comment = '', $graceful = true) {
        if ($this->canCancel() || $this->isPaymentReview()) {
            $cancelStatus = self::STATUS_CANCELLED;

            $this->setSubtotalCanceled($this->getSubtotal() - $this->getSubtotalInvoiced());
            $this->setBaseSubtotalCanceled($this->getBaseSubtotal() - $this->getBaseSubtotalInvoiced());

            $this->setTaxCanceled($this->getTaxAmount() - $this->getTaxInvoiced());
            $this->setBaseTaxCanceled($this->getBaseTaxAmount() - $this->getBaseTaxInvoiced());

            $this->setShippingCanceled($this->getShippingAmount() - $this->getShippingInvoiced());
            $this->setBaseShippingCanceled($this->getBaseShippingAmount() - $this->getBaseShippingInvoiced());

            $this->setDiscountCanceled(abs($this->getDiscountAmount()) - $this->getDiscountInvoiced());
            $this->setBaseDiscountCanceled(abs($this->getBaseDiscountAmount()) - $this->getBaseDiscountInvoiced());

            $this->setTotalCanceled($this->getGrandTotal() - $this->getTotalPaid());
            $this->setBaseTotalCanceled($this->getBaseGrandTotal() - $this->getBaseTotalPaid());

            $this->_setStatus($cancelStatus, $comment);
        } elseif (!$graceful) {
            throw new FrameworkException('Estimate does not allow to be canceled.');
        }
        return $this;
    }

    public function getItemsCollection($filterByTypes = array(), $nonChildrenOnly = false) {
        if (is_null($this->_items)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resourceModel = $objectManager->create('\Blackbox\EpaceImport\Model\Resource\Estimate\Item\Collection');
            $this->_items = $resourceModel->setEstimateFilter($this);

            if ($filterByTypes) {
                $this->_items->filterByTypes($filterByTypes);
            }
            if ($nonChildrenOnly) {
                $this->_items->filterByParent();
            }

            if ($this->getId()) {
                foreach ($this->_items as $item) {
                    $item->setEstimate($this);
                }
            }
        }
        return $this->_items;
    }

    /**
     * Get random items collection with related children
     *
     * @param int $limit
     * @return \Blackbox\EpaceImport\Model\Resource_Estimate_Item_Collection
     */
    public function getItemsRandomCollection($limit = 1) {
        return $this->_getItemsRandomCollection($limit);
    }

    /**
     * Get random items collection without related children
     *
     * @param int $limit
     * @return \Blackbox\EpaceImport\Model\Resource_Estimate_Item_Collection
     */
    public function getParentItemsRandomCollection($limit = 1) {
        return $this->_getItemsRandomCollection($limit, true);
    }

    /**
     * Get random items collection with or without related children
     *
     * @param int $limit
     * @param bool $nonChildrenOnly
     * @return \Blackbox\EpaceImport\Model\Resource_Estimate_Item_Collection
     */
    protected function _getItemsRandomCollection($limit, $nonChildrenOnly = false) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $item = $objectManager->create('\Blackbox\EpaceImport\Model\Estimate\Item');
        $collection = $item->getCollection()
                ->setEstimateFilter($this)
                ->setRandomEstimate();

        if ($nonChildrenOnly) {
            $collection->filterByParent();
        }
        $products = array();
        foreach ($collection as $item) {
            $products[] = $item->getProductId();
        }

        $productsCollection = $objectManager->create('\Magento\Catalog\Model\Product')
                        ->getCollection()
                        ->addIdFilter($products)
                        ->setVisibility($objectManager->create('\Magento\Catalog\Model\Product\Visiblity')->getVisibleInSiteIds())
                        /* Price data is added to consider item stock status using price index */
                        ->addPriceData()
                        ->setPageSize($limit)
                        ->load();

        foreach ($collection as $item) {
            $product = $productsCollection->getItemById($item->getProductId());
            if ($product) {
                $item->setProduct($product);
            }
        }

        return $collection;
    }

    /**
     * @return \Blackbox\EpaceImport\Model\Estimate_Item[]
     */
    public function getAllItems() {
        $items = array();
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted()) {
                $items[] = $item;
            }
        }
        return $items;
    }

    public function getAllVisibleItems() {
        $items = array();
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted() && !$item->getParentItemId()) {
                $items[] = $item;
            }
        }
        return $items;
    }

    public function getItemById($itemId) {
        return $this->getItemsCollection()->getItemById($itemId);
    }

    public function getItemByQuoteItemId($quoteItemId) {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getQuoteItemId() == $quoteItemId) {
                return $item;
            }
        }
        return null;
    }

    public function addItem(\Blackbox\EpaceImport\Model\Estimate\Item $item) {
        $item->setEstimate($this);
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }

    /**
     * Whether the estimate has nominal items only
     *
     * @return bool
     */
    public function isNominal() {
        foreach ($this->getAllVisibleItems() as $item) {
            if ('0' == $item->getIsNominal()) {
                return false;
            }
        }
        return true;
    }

    /*     * ********************* STATUSES ************************** */

    /**
     * Enter description here...
     *
     * @return \Blackbox\EpaceImport\Model\Resource_Estimate_Status_History_Collection
     */
    public function getStatusHistoryCollection($reload = false) {
        if (is_null($this->_statusHistory) || $reload) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resourceModel = $objectManager->create('\Blackbox\EpaceImport\Model\Resource\Estimate\Status\History\Collection');
            $this->_statusHistory = $resourceModel->setEstimateFilter($this)
                                        ->setEstimate('created_at', 'desc')
                                        ->setEstimate('entity_id', 'desc');

            if ($this->getId()) {
                foreach ($this->_statusHistory as $status) {
                    $status->setEstimate($this);
                }
            }
        }
        return $this->_statusHistory;
    }

    /**
     * Return collection of estimate status history items.
     *
     * @return \Blackbox\EpaceImport\Model\Estimate_Status_History[]
     */
    public function getAllStatusHistory() {
        $history = array();
        foreach ($this->getStatusHistoryCollection() as $status) {
            if (!$status->isDeleted()) {
                $history[] = $status;
            }
        }
        return $history;
    }

    /**
     * Return collection of visible on frontend estimate status history items.
     *
     * @return array
     */
    public function getVisibleStatusHistory() {
        $history = array();
        foreach ($this->getStatusHistoryCollection() as $status) {
            if (!$status->isDeleted() && $status->getComment() && $status->getIsVisibleOnFront()) {
                $history[] = $status;
            }
        }
        return $history;
    }

    public function getStatusHistoryById($statusId) {
        foreach ($this->getStatusHistoryCollection() as $status) {
            if ($status->getId() == $statusId) {
                return $status;
            }
        }
        return false;
    }

    /**
     * Set the estimate status history object and the estimate object to each other
     * Adds the object to the status history collection, which is automatically saved when the estimate is saved.
     * See the entity_id attribute backend model.
     * Or the history record can be saved standalone after this.
     *
     * @param \Blackbox\EpaceImport\Model\Estimate_Status_History $status
     * @return \Blackbox\EpaceImport\Model\Estimate
     */
    public function addStatusHistory(\Blackbox\EpaceImport\Model\Estimate\Status\History $history) {
        $history->setEstimate($this);
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
    public function getRealEstimateId() {
        $id = $this->getData('real_estimate_id');
        if (is_null($id)) {
            $id = $this->getIncrementId();
        }
        return $id;
    }

    /**
     * Get currency model instance. Will be used currency with which estimate placed
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getEstimateCurrency() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $currency = $objectManager->create('Magento\Directory\Model\Currency');
        if (is_null($this->_estimateCurrency)) {
            $this->_estimateCurrency = $currency->load($this->getEstimateCurrencyCode());
        }
        return $this->_estimateCurrency;
    }

    /**
     * Get formated price value including estimate currency rate to estimate website currency
     *
     * @param   float $price
     * @param   bool  $addBrackets
     * @return  string
     */
    public function formatPrice($price, $addBrackets = false) {
        return $this->formatPricePrecision($price, 2, $addBrackets);
    }

    public function formatPricePrecision($price, $precision, $addBrackets = false) {
        return $this->getEstimateCurrency()->formatPrecision($price, $precision, array(), true, $addBrackets);
    }

    /**
     * Retrieve text formated price value includeing estimate rate
     *
     * @param   float $price
     * @return  string
     */
    public function formatPriceTxt($price) {
        return $this->getEstimateCurrency()->formatTxt($price);
    }

    /**
     * Retrieve estimate website currency for working with base prices
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getBaseCurrency() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $currency = $objectManager->create('Magento\Directory\Model\Currency');
        if (is_null($this->_baseCurrency)) {
            $this->_baseCurrency = $currency->load($this->getBaseCurrencyCode());
        }
        return $this->_baseCurrency;
    }

    /**
     * Retrieve estimate website currency for working with base prices
     * @deprecated  please use getBaseCurrency instead.
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getStoreCurrency() {
        return $this->getData('store_currency');
    }

    public function formatBasePrice($price) {
        return $this->formatBasePricePrecision($price, 2);
    }

    public function formatBasePricePrecision($price, $precision) {
        return $this->getBaseCurrency()->formatPrecision($price, $precision);
    }

    public function isCurrencyDifferent() {
        return $this->getEstimateCurrencyCode() != $this->getBaseCurrencyCode();
    }

    /**
     * Retrieve estimate total due value
     *
     * @return float
     */
    public function getTotalDue() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $total = $this->getGrandTotal() - $this->getTotalPaid();
        $total = $storeManager->getStore($this->getStoreId())->roundPrice($total);
        return max($total, 0);
    }

    /**
     * Retrieve estimate total due value
     *
     * @return float
     */
    public function getBaseTotalDue() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $total = $this->getBaseGrandTotal() - $this->getBaseTotalPaid();
        $total = $storeManager->getStore($this->getStoreId())->roundPrice($total);
        return max($total, 0);
    }

    public function getData($key = '', $index = null) {
        if ($key == 'total_due') {
            return $this->getTotalDue();
        }
        if ($key == 'base_total_due') {
            return $this->getBaseTotalDue();
        }
        return parent::getData($key, $index);
    }

    /**
     * Retrieve array of related objects
     *
     * Used for estimate saving
     *
     * @return array
     */
    public function getRelatedObjects() {
        return $this->_relatedObjects;
    }

    /**
     * Retrieve customer name
     *
     * @return string
     */
    public function getCustomerName() {
        if ($this->getCustomerFirstname()) {
            $customerName = $this->getCustomerFirstname().' '.$this->getCustomerLastname();
        } else {
            $customerName = 'Guest';
        }
        return $customerName;
    }

    /**
     * Add New object to related array
     *
     * @param   Mage_Core_Model_Abstract $object
     * @return  \Blackbox\EpaceImport\Model\Estimate
     */
    public function addRelatedObject(Mage_Core_Model_Abstract $object) {
        $this->_relatedObjects[] = $object;
        return $this;
    }

    /**
     * Get formated estimate created date in store timezone
     *
     * @param   string $format date format type (short|medium|long|full)
     * @return  string
     */
    public function getCreatedAtFormated($format) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $timeZone = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        return $timeZone->formatDate($this->getCreatedAtStoreDate(), $format, true);
    }

    public function getEmailCustomerNote() {
        if ($this->getCustomerNoteNotify()) {
            return $this->getCustomerNote();
        }
        return '';
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave() {
        parent::_beforeSave();
        if (!$this->getId()) {
            $store = $this->getStore();
            $name = array($store->getWebsite()->getName(), $store->getGroup()->getName(), $store->getName());
            $this->setStoreName(implode("\n", $name));
        }

        if (!$this->getIncrementId()) {
            $incrementId = Mage::getSingleton('eav/config')
                    ->getEntityType('estimate')
                    ->fetchNewIncrementId($this->getStoreId());
            $this->setIncrementId($incrementId);
        }

        /**
         * Process items dependency for new estimate
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
        if ($this->getCustomer()) {
            $this->setCustomerId($this->getCustomer()->getId());
        }

        if ($this->hasBillingAddressId() && $this->getBillingAddressId() === null) {
            $this->unsBillingAddressId();
        }

        if ($this->hasShippingAddressId() && $this->getShippingAddressId() === null) {
            $this->unsShippingAddressId();
        }

        $this->setData('protect_code', substr(md5(uniqid(mt_rand(), true) . ':' . microtime(true)), 5, 6));
        return $this;
    }

    /**
     * Save estimate related objects
     *
     * @return \Blackbox\EpaceImport\Model\Estimate
     */
    protected function _afterSave() {
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

    public function getStoreGroupName() {
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
     * @return \Blackbox\EpaceImport\Model\Estimate
     */
    public function reset() {
        $this->unsetData();
        $this->_actionFlag = array();
        $this->_items = null;
        $this->_statusHistory = null;
        $this->_relatedObjects = array();
        $this->_estimateCurrency = null;
        $this->_baseCurrency = null;

        return $this;
    }

    public function getIsNotVirtual() {
        return !$this->getIsVirtual();
    }

    /**
     * Check whether estimate is canceled
     *
     * @return bool
     */
    public function isCanceled() {
        return ($this->getStatus() === self::STATUS_CANCELLED);
    }

    /**
     * Protect estimate delete from not admin scope
     * @return \Blackbox\EpaceImport\Model\Estimate
     */
    protected function _beforeDelete() {
        $this->_protectFromNonAdmin();
        return parent::_beforeDelete();
    }

}
