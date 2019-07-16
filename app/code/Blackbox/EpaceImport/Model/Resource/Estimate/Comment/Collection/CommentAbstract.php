<?php

namespace Blackbox\EpaceImport\Model\Resource\Estimate\Comment\Collection;
abstract class CommentAbstract  extends Magento\Sales\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * Set filter on comments by their parent item
     *
     * @param \Magento\Framework\Model\AbstractModel|int $parent
     * @return Blackbox\EpaceImport\Model\Resource\Estimate\Comment\Collection\Estimate_Comment_Collection_Abstract
     */
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
     * @return Blackbox_EpaceImport_Model_Resource_Estimate_Comment_Collection_Abstract
     */
    public function addVisibleOnFrontFilter($flag = 1)
    {
        return $this->addFieldToFilter('is_visible_on_front', $flag);
    }

    /**
     * Set created_at sort order
     *
     * @param string $direction
     * @return Blackbox_EpaceImport_Model_Resource_Estimate_Comment_Collection_Abstract
     */
    public function setCreatedAtOrder($direction = 'desc')
    {
        return $this->setOrder('created_at', $direction);
    }
}
