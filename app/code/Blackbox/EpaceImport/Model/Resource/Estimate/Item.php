<?php
namespace Blackbox\EpaceImport\Model\Resource\Estimate;
/**
 * Flat sales order item resource
 *
 * @category    Mage
 * @package     \Blackbox\EpaceImport
 * @author      Magento Framework Team <core@magentocommerce.com>
 */
class Item extends \Blackbox\EpaceImport\Model\Resource\Estimate\EstimateAbstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'epacei_estimate_item_resource';

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('epacei/estimate_item', 'item_id');
    }
}
