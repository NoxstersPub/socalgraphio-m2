<?php

/**
 * PurchaseOrder Item Model
 *
 * @method Blackbox_EpaceImport_Model_Resource_PurchaseOrder_Item _getResource()
 * @method Blackbox_EpaceImport_Model_Resource_PurchaseOrder_Item getResource()
 * @method int getPurchaseOrderId()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setPurchaseOrderId(int $value)
 * @method int getParentItemId()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setParentItemId(int $value)
 * @method int getQuoteItemId()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setQuoteItemId(int $value)
 * @method int getStoreId()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setStoreId(int $value)
 * @method string getCreatedAt()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setUpdatedAt(string $value)
 * @method int getProductId()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setProductId(int $value)
 * @method string getProductType()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setProductType(string $value)
 * @method float getWeight()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setWeight(float $value)
 * @method int getIsVirtual()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setIsVirtual(int $value)
 * @method string getSku()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setSku(string $value)
 * @method string getName()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setName(string $value)
 * @method string getDescription()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setDescription(string $value)
 * @method string getAppliedRuleIds()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setAppliedRuleIds(string $value)
 * @method string getAdditionalData()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setAdditionalData(string $value)
 * @method int getFreeShipping()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setFreeShipping(int $value)
 * @method int getIsQtyDecimal()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setIsQtyDecimal(int $value)
 * @method int getNoDiscount()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setNoDiscount(int $value)
 * @method float getQtyBackordered()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setQtyBackordered(float $value)
 * @method float getQtyCanceled()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setQtyCanceled(float $value)
 * @method float getQtyInvoiced()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setQtyInvoiced(float $value)
 * @method float getQty()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setQty(float $value)
 * @method float getQtyRefunded()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setQtyRefunded(float $value)
 * @method float getQtyShipped()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setQtyShipped(float $value)
 * @method float getBaseCost()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseCost(float $value)
 * @method float getPrice()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setPrice(float $value)
 * @method float getBasePrice()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBasePrice(float $value)
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setOriginalPrice(float $value)
 * @method float getBaseOriginalPrice()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseOriginalPrice(float $value)
 * @method float getTaxPercent()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setTaxPercent(float $value)
 * @method float getTaxAmount()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setTaxAmount(float $value)
 * @method float getBaseTaxAmount()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseTaxAmount(float $value)
 * @method float getTaxInvoiced()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setTaxInvoiced(float $value)
 * @method float getBaseTaxInvoiced()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseTaxInvoiced(float $value)
 * @method float getDiscountPercent()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setDiscountPercent(float $value)
 * @method float getDiscountAmount()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setDiscountAmount(float $value)
 * @method float getBaseDiscountAmount()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseDiscountAmount(float $value)
 * @method float getDiscountInvoiced()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setDiscountInvoiced(float $value)
 * @method float getBaseDiscountInvoiced()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseDiscountInvoiced(float $value)
 * @method float getAmountRefunded()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setAmountRefunded(float $value)
 * @method float getBaseAmountRefunded()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseAmountRefunded(float $value)
 * @method float getRowTotal()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setRowTotal(float $value)
 * @method float getBaseRowTotal()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseRowTotal(float $value)
 * @method float getRowInvoiced()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setRowInvoiced(float $value)
 * @method float getBaseRowInvoiced()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseRowInvoiced(float $value)
 * @method float getRowWeight()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setRowWeight(float $value)
 * @method int getGiftMessageId()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setGiftMessageId(int $value)
 * @method int getGiftMessageAvailable()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setGiftMessageAvailable(int $value)
 * @method float getBaseTaxBeforeDiscount()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseTaxBeforeDiscount(float $value)
 * @method float getTaxBeforeDiscount()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setTaxBeforeDiscount(float $value)
 * @method string getExtOrderItemId()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setExtOrderItemId(string $value)
 * @method string getWeeeTaxApplied()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setWeeeTaxApplied(string $value)
 * @method float getWeeeTaxAppliedAmount()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setWeeeTaxAppliedAmount(float $value)
 * @method float getWeeeTaxAppliedRowAmount()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setWeeeTaxAppliedRowAmount(float $value)
 * @method float getBaseWeeeTaxAppliedAmount()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseWeeeTaxAppliedAmount(float $value)
 * @method float getBaseWeeeTaxAppliedRowAmount()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseWeeeTaxAppliedRowAmount(float $value)
 * @method float getWeeeTaxDisposition()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setWeeeTaxDisposition(float $value)
 * @method float getWeeeTaxRowDisposition()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setWeeeTaxRowDisposition(float $value)
 * @method float getBaseWeeeTaxDisposition()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseWeeeTaxDisposition(float $value)
 * @method float getBaseWeeeTaxRowDisposition()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseWeeeTaxRowDisposition(float $value)
 * @method int getLockedDoInvoice()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setLockedDoInvoice(int $value)
 * @method int getLockedDoShip()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setLockedDoShip(int $value)
 * @method float getPriceInclTax()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setPriceInclTax(float $value)
 * @method float getBasePriceInclTax()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBasePriceInclTax(float $value)
 * @method float getRowTotalInclTax()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setRowTotalInclTax(float $value)
 * @method float getBaseRowTotalInclTax()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseRowTotalInclTax(float $value)
 * @method float getHiddenTaxAmount()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setHiddenTaxAmount(float $value)
 * @method float getBaseHiddenTaxAmount()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseHiddenTaxAmount(float $value)
 * @method float getHiddenTaxInvoiced()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setHiddenTaxInvoiced(float $value)
 * @method float getBaseHiddenTaxInvoiced()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseHiddenTaxInvoiced(float $value)
 * @method float getHiddenTaxRefunded()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setHiddenTaxRefunded(float $value)
 * @method float getBaseHiddenTaxRefunded()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseHiddenTaxRefunded(float $value)
 * @method int getIsNominal()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setIsNominal(int $value)
 * @method float getTaxCanceled()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setTaxCanceled(float $value)
 * @method float getHiddenTaxCanceled()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setHiddenTaxCanceled(float $value)
 * @method float getTaxRefunded()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setTaxRefunded(float $value)
 * @method float getBaseTaxRefunded()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseTaxRefunded(float $value)
 * @method float getDiscountRefunded()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setDiscountRefunded(float $value)
 * @method float getBaseDiscountRefunded()
 * @method Blackbox_EpaceImport_Model_PurchaseOrder_Item setBaseDiscountRefunded(float $value)
 *
 * @category    Mage
 * @package     Blackbox_EpaceImport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Blackbox_EpaceImport_Model_PurchaseOrder_Item extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'epacei_purchase_order_item';
    protected $_eventObject = 'item';

    protected static $_statuses = null;

    /**
     * PurchaseOrder instance
     *
     * @var Blackbox_EpaceImport_Model_PurchaseOrder
     */
    protected $_purchaseOrder       = null;
    protected $_parentItem  = null;
    protected $_children    = array();

    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('epacei/purchaseOrder_item');
    }

    /**
     * Prepare data before save
     *
     * @return Blackbox_EpaceImport_Model_PurchaseOrder_Item
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if (!$this->getPurchaseOrderId() && $this->getPurchaseOrder()) {
            $this->setPurchaseOrderId($this->getPurchaseOrder()->getId());
        }
        if ($this->getParentItem()) {
            $this->setParentItemId($this->getParentItem()->getId());
        }
        return $this;
    }

    /**
     * Set parent item
     *
     * @param   Blackbox_EpaceImport_Model_PurchaseOrder_Item $item
     * @return  Blackbox_EpaceImport_Model_PurchaseOrder_Item
     */
    public function setParentItem($item)
    {
        if ($item) {
            $this->_parentItem = $item;
            $item->setHasChildren(true);
            $item->addChildItem($this);
        }
        return $this;
    }

    /**
     * Get parent item
     *
     * @return Blackbox_EpaceImport_Model_PurchaseOrder_Item || null
     */
    public function getParentItem()
    {
        return $this->_parentItem;
    }

    /**
     * Declare purchase order
     *
     * @param   Blackbox_EpaceImport_Model_PurchaseOrder $purchaseOrder
     * @return  Blackbox_EpaceImport_Model_PurchaseOrder_Item
     */
    public function setPurchaseOrder(Blackbox_EpaceImport_Model_PurchaseOrder $purchaseOrder)
    {
        $this->_purchaseOrder = $purchaseOrder;
        $this->setPurchaseOrderId($purchaseOrder->getId());
        return $this;
    }

    /**
     * Retrieve purchase order model object
     *
     * @return Blackbox_EpaceImport_Model_PurchaseOrder
     */
    public function getPurchaseOrder()
    {
        if (is_null($this->_purchaseOrder) && ($purchaseOrderId = $this->getPurchaseOrderId())) {
            $purchaseOrder = Mage::getModel('epacei/purchaseOrder');
            $purchaseOrder->load($purchaseOrderId);
            $this->setPurchaseOrder($purchaseOrder);
        }
        return $this->_purchaseOrder;
    }

    public function getQtyOrdered()
    {
        return $this->getQty();
    }

    public function setQtyOrdered($qty)
    {
        return $this->setQty($qty);
    }

    /**
     * Retrieve backordered qty of children items
     *
     * @return float|null
     */
    protected function _getQtyChildrenBackordered()
    {
        $backordered = null;
        foreach ($this->_children as $childItem) {
            $backordered += (float)$childItem->getQtyBackordered();
        }

        return $backordered;
    }

    /**
     * Redeclare getter for back compatibility
     *
     * @return float
     */
    public function getOriginalPrice()
    {
        $price = $this->getData('original_price');
        if (is_null($price)) {
            return $this->getPrice();
        }
        return $price;
    }

    /**
     * Set product options
     *
     * @param   array $options
     * @return  Blackbox_EpaceImport_Model_PurchaseOrder_Item
     */
    public function setProductOptions(array $options)
    {
        $this->setData('product_options', serialize($options));
        return $this;
    }

    /**
     * Get product options array
     *
     * @return array
     */
    public function getProductOptions()
    {
        if ($options = $this->_getData('product_options')) {
            return unserialize($options);
        }
        return array();
    }

    /**
     * Get product options array by code.
     * If code is null return all options
     *
     * @param string $code
     * @return array
     */
    public function getProductOptionByCode($code=null)
    {
        $options = $this->getProductOptions();
        if (is_null($code)) {
            return $options;
        }
        if (isset($options[$code])) {
            return $options[$code];
        }
        return null;
    }

    /**
     * Return real product type of item or NULL if item is not composite
     *
     * @return string | null
     */
    public function getRealProductType()
    {
        if ($productType = $this->getProductOptionByCode('real_product_type')) {
            return $productType;
        }
        return null;
    }

    /**
     * Adds child item to this item
     *
     * @param Blackbox_EpaceImport_Model_PurchaseOrder_Item $item
     */
    public function addChildItem($item)
    {
        if ($item instanceof Blackbox_EpaceImport_Model_PurchaseOrder_Item) {
            $this->_children[] = $item;
        } else if (is_array($item)) {
            $this->_children = array_merge($this->_children, $item);
        }
    }

    /**
     * Return chilgren items of this item
     *
     * @return array
     */
    public function getChildrenItems() {
        return $this->_children;
    }

    /**
     * Return checking of what calculation
     * type was for this product
     *
     * @return bool
     */
    public function isChildrenCalculated() {
        if ($parentItem = $this->getParentItem()) {
            $options = $parentItem->getProductOptions();
        } else {
            $options = $this->getProductOptions();
        }

        if (isset($options['product_calculations']) &&
             $options['product_calculations'] == Mage_Catalog_Model_Product_Type_Abstract::CALCULATE_CHILD) {
                return true;
        }
        return false;
    }
    /**
     * Check if discount has to be applied to parent item
     *
     * @return bool
     */
    public function getForceApplyDiscountToParentItem()
    {
        if ($this->getParentItem()) {
            $product = $this->getParentItem()->getProduct();
        } else {
            $product = $this->getProduct();
        }

        return $product->getTypeInstance()->getForceApplyDiscountToParentItem();
    }

    /**
     * Return checking of what shipment
     * type was for this product
     *
     * @return bool
     */
    public function isShipSeparately() {
        if ($parentItem = $this->getParentItem()) {
            $options = $parentItem->getProductOptions();
        } else {
            $options = $this->getProductOptions();
        }

        if (isset($options['shipment_type']) &&
             $options['shipment_type'] == Mage_Catalog_Model_Product_Type_Abstract::SHIPMENT_SEPARATELY) {
                return true;
        }
        return false;
    }

    /**
     * This is Dummy item or not
     * if $shipment is true then we checking this for shipping situation if not
     * then we checking this for calculation
     *
     * @param bool $shipment
     * @return bool
     */
    public function isDummy($shipment = false){
        if ($shipment) {
            if ($this->getHasChildren() && $this->isShipSeparately()) {
                return true;
            }

            if ($this->getHasChildren() && !$this->isShipSeparately()) {
                return false;
            }

            if ($this->getParentItem() && $this->isShipSeparately()) {
                return false;
            }

            if ($this->getParentItem() && !$this->isShipSeparately()) {
                return true;
            }
        } else {
            if ($this->getHasChildren() && $this->isChildrenCalculated()) {
                return true;
            }

            if ($this->getHasChildren() && !$this->isChildrenCalculated()) {
                return false;
            }

            if ($this->getParentItem() && $this->isChildrenCalculated()) {
                return false;
            }

            if ($this->getParentItem() && !$this->isChildrenCalculated()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns formatted buy request - object, holding request received from
     * product view page with keys and options for configured product
     *
     * @return Varien_Object
     */
    public function getBuyRequest()
    {
        $option = $this->getProductOptionByCode('info_buyRequest');
        if (!$option) {
            $option = array();
        }
        $buyRequest = new Varien_Object($option);
        $buyRequest->setQty($this->getQty() * 1);
        return $buyRequest;
    }

    /**
     * Retrieve product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!$this->getData('product')) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            $this->setProduct($product);
        }

        return $this->getData('product');
    }

    /**
     * Get the discount amount applied on weee in base
     *
     * @return float
     */
    public function getBaseDiscountAppliedForWeeeTax()
    {
        $weeeTaxAppliedAmounts = unserialize($this->getWeeeTaxApplied());
        $totalDiscount = 0;
        if (!is_array($weeeTaxAppliedAmounts)) {
            return $totalDiscount;
        }
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            if (isset($weeeTaxAppliedAmount['total_base_weee_discount'])) {
                return $weeeTaxAppliedAmount['total_base_weee_discount'];
            } else {
                $totalDiscount += isset($weeeTaxAppliedAmount['base_weee_discount'])
                    ? $weeeTaxAppliedAmount['base_weee_discount'] : 0;
            }
        }
        return $totalDiscount;
    }

    /**
     * Get the discount amount applied on Weee
     *
     * @return float
     */
    public function getDiscountAppliedForWeeeTax()
    {
        $weeeTaxAppliedAmounts = unserialize($this->getWeeeTaxApplied());
        $totalDiscount = 0;
        if (!is_array($weeeTaxAppliedAmounts)) {
            return $totalDiscount;
        }
        foreach ($weeeTaxAppliedAmounts as $weeeTaxAppliedAmount) {
            if (isset($weeeTaxAppliedAmount['total_weee_discount'])) {
                return $weeeTaxAppliedAmount['total_weee_discount'];
            } else {
                $totalDiscount += isset($weeeTaxAppliedAmount['weee_discount'])
                    ? $weeeTaxAppliedAmount['weee_discount'] : 0;
            }
        }
        return $totalDiscount;
    }
}
