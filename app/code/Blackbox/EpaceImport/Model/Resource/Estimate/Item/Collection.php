<?php
namespace Blackbox\EpaceImport\Model\Resource\Estimate\Item;

/**
 * Flat sales estimate payment collection
 *
 * @category    Mage
 * @package     \Mage\Sales
 * @author      Magento Framework Team <core@magentocommerce.com>
 */
class Collection extends \Blackbox\EpaceImport\Model\Resource\Estimate\Collection\EstimateAbstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'epacei_estimate_item_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject    = 'estimate_item_collection';

    /**
     * Estimate field for setEstimateFilter
     *
     * @var string
     */
    protected $_estimateField     = 'estimate_id';

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('epacei/estimate_item');
    }

    /**
     * Assign parent items on after collection load
     *
     * @return \Blackbox\EpaceImport\Model\Resource\Estimate\Item\Collection
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
     * Set random items estimate
     *
     * @return \Blackbox\EpaceImport\Model\Resource\Estimate\Item\Collection
     */
    public function setRandomEstimate()
    {
        $this->getConnection()->estimateRand($this->getSelect());
        return $this;
    }

    /**
     * Set filter by item id
     *
     * @param mixed $item
     * @return \Blackbox\EpaceImport\Model\Resource\Estimate\Item\Collection
     */
    public function addIdFilter($item)
    {
        if (is_array($item)) {
            $this->addFieldToFilter('item_id', array('in'=>$item));
        } elseif ($item instanceof \Magento\Sales\Model\Estimate\Item) {
            $this->addFieldToFilter('item_id', $item->getId());
        } else {
            $this->addFieldToFilter('item_id', $item);
        }
        return $this;
    }

    /**
     * Filter collection by specified product types
     *
     * @param array $typeIds
     * @return \Blackbox\EpaceImport\Model\Resource\Estimate\Item\Collection
     */
    public function filterByTypes($typeIds)
    {
        $this->addFieldToFilter('product_type', array('in' => $typeIds));
        return $this;
    }

    /**
     * Filter collection by parent_item_id
     *
     * @param int $parentId
     * @return \Blackbox\EpaceImport\Model\Resource\Estimate\Item\Collection
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

    /**
     * Filter by customerId
     *
     * @param int|array $customerId
     * @return \Blackbox\EpaceImport\Model\Resource\Estimate\Item\Collection
     */
    public function addFilterByCustomerId($customerId)
    {
        $this->getSelect()->joinInner(
            array('estimate' => $this->getTable('sales/estimate')),
            'main_table.estimate_id = estimate.entity_id', array())
            ->where('estimate.customer_id IN(?)', $customerId);

        return $this;
    }
}
