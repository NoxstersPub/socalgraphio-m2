<?php
namespace Blackbox\EpaceImport\Model\Resource\Estimate\Status\History;

/**
 * Flat sales order status history collection
 *
 * @category    Mage
 * @package     \Blackbox\EpaceImport
 * @author      Magento Framework Team <core@magentocommerce.com>
 */
class Collection extends \Blackbox\EpaceImport\Model\Resource\Estimate\Collection\EstimateAbstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'epacei_estimate_status_history_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject    = 'estimate_status_history_collection';

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('epacei/estimate_status_history');
    }

    /**
     * Get history object collection for specified instance (order, shipment, invoice or credit memo)
     * Parameter instance may be one of the following types: \Blackbox\EpaceImport\Model\Estimate,
     * \Blackbox\EpaceImport\Model\Estimate\Creditmemo, \Blackbox\EpaceImport\Model\Estimate\Invoice, \Blackbox\EpaceImport\Model\Estimate\Shipment
     *
     * @param mixed $instance
     * @param string $historyEntityName
     *
     * @return \Blackbox\EpaceImport\Model\Estimate\Status\History|null
     */
    public function getUnnotifiedForInstance($instance, $historyEntityName = \Blackbox\EpaceImport\Model\Estimate::HISTORY_ENTITY_NAME)
    {
        if(!$instance instanceof \Blackbox\EpaceImport\Model\Estimate) {
            $instance = $instance->getOrder();
        }
        $this->setEstimate($instance)->setOrder('created_at', 'desc')
            ->addFieldToFilter('entity_name', $historyEntityName)
            ->addFieldToFilter('is_customer_notified', 0)->setPageSize(1);
        foreach($this as $historyItem) {
            return $historyItem;
        }
        return null;
    }

}
