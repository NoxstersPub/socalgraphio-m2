<?php

namespace Blackbox\EpaceImport\Model\Resource;

class PurchaseOrder extends \Magento\Sales\Model\ResourceModel\EntityAbstract {

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'epacei_purchase_order_resource';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'resource';

    /**
     * Model Initialization
     *
     */
    protected function _construct() {
        $this->_init('epacei/purchase_order', 'entity_id');
    }

}
