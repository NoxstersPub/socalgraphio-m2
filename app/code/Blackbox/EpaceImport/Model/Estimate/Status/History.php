<?php

namespace Blackbox\EpaceImport\Model\Estimate\Status;

class History extends \Magento\Framework\Model\AbstractModel
{
    const CUSTOMER_NOTIFICATION_NOT_APPLICABLE = 2;

    /**
     * Estimate instance
     *
     * @var \Blackbox\EpaceImport\Model\Estimate
     */
    protected $_estimate;

    /**
     * Whether setting order again is required (for example when setting non-saved yet order)
     * @deprecated after 1.4, wrong logic of setting order id
     * @var bool
     */
    private $_shouldSetEstimateBeforeSave = false;

    protected $_eventPrefix = 'sales_estimate_status_history';
    protected $_eventObject = 'status_history';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('Blackbox\EpaceImport\Model\Estimate\Status\History');
    }

    /**
     * Set order object and grab some metadata from it
     *
     * @param   \Blackbox\EpaceImport\Model\Estimate $order
     * @return  \Blackbox\EpaceImport\Model\Estimate_Status_History
     */
    public function setEstimate(\Blackbox\EpaceImport\Model\Estimate $order)
    {
        $this->_estimate = $order;
        $this->setStoreId($order->getStoreId());
        return $this;
    }

    /**
     * Notification flag
     *
     * @param  mixed $flag OPTIONAL (notification is not applicable by default)
     * @return \Blackbox\EpaceImport\Model\Estimate_Status_History
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
     * @return \Blackbox\EpaceImport\Model\Estimate
     */
    public function getEstimate()
    {
        return $this->_estimate;
    }

    /**
     * Retrieve status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        if($this->getEstimate()) {
            return $this->getEstimate()->getConfig()->getStatusLabel($this->getStatus());
        }
    }

    /**
     * Get store object
     *
     * @return unknown
     */
    public function getStore()
    {
        if ($this->getEstimate()) {
            return $this->getEstimate()->getStore();
        }
        /** 
         * It is implemented as Magento 1 standards
         */
        return Mage::app()->getStore();
    }

    /**
     * Set order again if required
     *
     * @return \Blackbox\EpaceImport\Model\Estimate_Status_History
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getParentId() && $this->getEstimate()) {
            $this->setParentId($this->getEstimate()->getId());
        }

        return $this;
    }
}
