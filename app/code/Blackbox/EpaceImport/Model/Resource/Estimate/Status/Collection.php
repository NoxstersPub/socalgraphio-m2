<?php
namespace Blackbox\EpaceImport\Model\Resource\Estimate\Status;

/**
 * Flat sales order status history collection
 *
 * @category    Mage
 * @package     \Blackbox\EpaceImport
 * @author      Magento Framework Team <core@magentocommerce.com>
 */
class Collection extends \Blackbox\Epace\Model\Resource\Epace\Estimate\Status\Collection
{
    /**
     * Define label order
     *
     * @param string $dir
     * @return \Blackbox\EpaceImport\Model\Resource\Estimate\Status\Collection
     */
    public function orderByLabel($dir = 'ASC')
    {
        $this->setOrder('description', $dir);
        return $this;
    }
}
