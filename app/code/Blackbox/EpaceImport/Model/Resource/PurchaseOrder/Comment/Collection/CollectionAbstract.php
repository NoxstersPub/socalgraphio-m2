<?php
namespace Blackbox\EpaceImport\Model\Resource\PurchaseOrder\Comment\Collection;

abstract class CollectionAbstract extends \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection
{

    public function setParentFilter($parent)
    {
        if ($parent instanceof \Magento\Framework\Model\AbstractModel) {
            $parent = $parent->getId();
        }
        return $this->addFieldToFilter('parent_id', $parent);
    }

    /**
     * Adds filter to get only 'visible on front' comments
     *
     * @param int $flag
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\Comment\Collection\Abstract
     */
    public function addVisibleOnFrontFilter($flag = 1)
    {
        return $this->addFieldToFilter('is_visible_on_front', $flag);
    }

    /**
     * Set created_at sort order
     *
     * @param string $direction
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\Comment\Collection\Abstract
     */
    public function setCreatedAtOrder($direction = 'desc')
    {
        return $this->setOrder('created_at', $direction);
    }
}
