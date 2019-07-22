<?php

namespace Blackbox\EpaceImport\Model\PurchaseOrder;

class Item extends \Magento\Framework\Model\AbstractModel
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (is_null($this->_purchaseOrder) && ($purchaseOrderId = $this->getPurchaseOrderId())) {
            $purchaseOrder = $objectManager->create('Blackbox\EpaceImport\Model\PurchaseOrder');
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$this->getData('product')) {
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($this->getProductId());
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
