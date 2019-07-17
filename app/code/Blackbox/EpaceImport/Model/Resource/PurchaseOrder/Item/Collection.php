<?php
namespace Blackbox\EpaceImport\Model\Resource\PurchaseOrder\Item;

/**
 * Flat purchase order collection
 */
class Collection extends \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\Collection\PurchaseOrderAbstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'epacei_purchase_order_item_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject    = 'purchase_order_item_collection';

    /**
     * PurchaseOrder field for setPurchaseOrderFilter
     *
     * @var string
     */
    protected $_purchaseOrderField     = 'purchase_order_id';

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('epacei/purchaseOrder_item');
    }

    /**
     * Assign parent items on after collection load
     *
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\Item\Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        /**
         * Assign parent items
         */
        foreach ($this as $item) {
            if ($item->getParentItemId()) {
                $item->setParentItem($this->getItemById($item->getParentItemId()));
            }
        }
        return $this;
    }

    /**
     * Set random items order
     *
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\Item\Collection
     */
    public function setRandomOrder()
    {
        $this->getConnection()->orderRand($this->getSelect());
        return $this;
    }

    /**
     * Set filter by item id
     *
     * @param mixed $item
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\Item\Collection
     */
    public function addIdFilter($item)
    {
        if (is_array($item)) {
            $this->addFieldToFilter('item_id', array('in'=>$item));
        } elseif ($item instanceof \Blackbox\EpaceImport\Model\PurchaseOrder\Item) {
            $this->addFieldToFilter('item_id', $item->getId());
        } else {
            $this->addFieldToFilter('item_id', $item);
        }
        return $this;
    }

    /**
     * Filter collection by parent_item_id
     *
     * @param int $parentId
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\Item\Collection
     */
    public function filterByParent($parentId = null)
    {
        if (empty($parentId)) {
            $this->addFieldToFilter('parent_item_id', array('null' => true));
        } else {
            $this->addFieldToFilter('parent_item_id', $parentId);
        }
        return $this;
    }
}
