<?php
namespace Blackbox\EpaceImport\Model\Resource\PurchaseOrder;

/**
 * Flat sales order item resource
 *
 * @category    Mage
 * @package     \Blackbox\EpaceImport
 * @author      Magento Framework Team <core@magentocommerce.com>
 */
class Item extends \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\PurchaseOrderAbstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'epacei_purchase_order_item_resource';

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('epacei/purchase_order_item', 'item_id');
    }
}
