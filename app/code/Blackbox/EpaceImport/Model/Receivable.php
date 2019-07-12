<?php

namespace Blackbox\EpaceImport\Model;

class Receivable extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Invoice states
     */
    const STATE_OPEN        = 0;
    const STATE_CLOSED      = 1;
    const STATE_DISPUTED    = 2;

    protected static $_states;
    
    protected $_rounders = [];

    protected $_order;
    protected $_invoice;

    protected $_eventPrefix = 'epacei_receivable';
    protected $_eventObject = 'receivable';

    /**
     * Initialize invoice resource model
     */
    protected function _construct()
    {
        $this->_init('Blackbox\EpaceImport\Model\Receivable');
    }

    /**
     * Load invoice by increment id
     *
     * @param string $incrementId
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function loadByIncrementId($incrementId)
    {
        $ids = $this->getCollection()
            ->addAttributeToFilter('increment_id', $incrementId)
            ->getAllIds();

        if (!empty($ids)) {
            reset($ids);
            $this->load(current($ids));
        }
        return $this;
    }

    /**
     * Retrieve store model instance
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return $this->getOrder()->getStore();
    }

    /**
     * Declare order for invoice
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  Mage_Sales_Model_Order_Invoice
     */
    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->_order = $order;
        $this->setOrderId($order->getId())
            ->setStoreId($order->getStoreId());
        return $this;
    }

    /**
     * Retrieve the order the invoice for created for
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->_order instanceof \Magento\Sales\Model\Order) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('\Magento\Sales\Model\Order');
            $this->_order = $order->load($this->getOrderId());
        }
        return $this->_order;
    }
    
    public function setInvoice(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $this->_invoice = $invoice;
        $this->setInvoiceId($invoice->getId())
            ->setOrderId($invoice->getOrderId());
        return $this;
    }
    
    public function getInvoice()
    {
        if (!$this->_invoice instanceof \Magento\Sales\Model\Order\Invoice) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $invoice = $objectManager->create('\Magento\Sales\Model\Order\Invoice');
            $this->_invoice = $invoice->load($this->getInvoiceId());
        }
        return $this->_invoice;
    }

    /**
     * Retrieve the increment_id of the order
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('\Magento\Sales\Model\Order');
        return $order->getResource()->getIncrementId($this->getOrderId());
    }

    /**
     * Round price considering delta
     *
     * @param float $price
     * @param string $type
     * @param bool $negative Indicates if we perform addition (true) or subtraction (false) of rounded value
     * @return float
     */
    public function roundPrice($price, $type = 'regular', $negative = false)
    {
            $price = $this->_rounders[$type]->deltaRound($price, $negative);
        return $price;
    }

    /**
     * Retrieve receivable states array
     *
     * @return array
     */
    public static function getStates()
    {
        if (is_null(self::$_states)) {
            self::$_states = array(
                self::STATE_OPEN       => 'Open',
                self::STATE_CLOSED       => 'Closed',
                self::STATE_DISPUTED   => 'Disputed',
            );
        }
        return self::$_states;
    }

    /**
     * Retrieve receivable state name by state identifier
     *
     * @param   int $stateId
     * @return  string
     */
    public function getStateName($stateId = null)
    {
        if (is_null($stateId)) {
            $stateId = $this->getState();
        }

        if (is_null(self::$_states)) {
            self::getStates();
        }
        if (isset(self::$_states[$stateId])) {
            return self::$_states[$stateId];
        }
        return 'Unknown State';
    }


    /**
     * Enter description here...
     *
     * @return string
     */
    public function getRealReceivableId()
    {
        $id = $this->getData('real_receivable_id');
        if (is_null($id)) {
            $id = $this->getIncrementId();
        }
        return $id;
    }

    /**
     * Get currency model instance. Will be used currency with which receivable placed
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getReceivableCurrency()
    {
        if (is_null($this->_receivableCurrency)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $currency = $objectManager->create('Magento\Directory\Model\Currency');
            $this->_receivableCurrency = $currency->load($this->getReceivableCurrencyCode());
        }
        return $this->_receivableCurrency;
    }

    /**
     * Get formated price value including receivable currency rate to receivable website currency
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
        return $this->getReceivableCurrency()->formatPrecision($price, $precision, array(), true, $addBrackets);
    }

    /**
     * Retrieve text formated price value includeing receivable rate
     *
     * @param   float $price
     * @return  string
     */
    public function formatPriceTxt($price)
    {
        return $this->getReceivableCurrency()->formatTxt($price);
    }

    /**
     * Retrieve receivable website currency for working with base prices
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

    /**
     * Retrieve receivable website currency for working with base prices
     * @deprecated  please use getBaseCurrency instead.
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getStoreCurrency()
    {
        return $this->getData('store_currency');
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
        return $this->getReceivableCurrencyCode() != $this->getBaseCurrencyCode();
    }

    /**
     * Retrieve customer name
     *
     * @return string
     */
    public function getCustomerName()
    {
        if ($this->getCustomerFirstname()) {
            $customerName = $this->getCustomerFirstname().' '.$this->getCustomerLastname();
        } else {
            $customerName = 'Guest';
        }
        return $customerName;
    }

    public function getStatusLabel()
    {
        return $this->getStatuses()[$this->getState()];
    }

    public function getStatuses()
    {
        return [
            self::STATE_OPEN => 'Open',
            self::STATE_CLOSED => 'Closed',
            self::STATE_DISPUTED => 'Disputed'
        ];
    }

    /**
     * Reset invoice object
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function reset()
    {
        $this->unsetData();
        $this->_origData = null;
        $this->_order = null;
        $this->_invoice = null;
        return $this;
    }

    /**
     * Before object save manipulations
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getOrderId() && $this->getOrder()) {
            $this->setOrderId($this->getOrder()->getId());
        }

        return $this;
    }
}
