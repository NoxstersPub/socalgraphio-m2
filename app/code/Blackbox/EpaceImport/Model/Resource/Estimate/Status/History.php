<?php
namespace Blackbox\EpaceImport\Model\Resource\Estimate\Status;

/**
 * Flat sales order status history resource
 *
 * @category    Mage
 * @package     \Blackbox\EpaceImport
 * @author      Magento Framework Team <core@magentocommerce.com>
 */
class History extends \Blackbox\EpaceImport\Model\Resource\Estimate\EstimateAbstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'epace_estimate_status_history_resource';

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('epacei/estimate_status_history', 'entity_id');
    }
}
