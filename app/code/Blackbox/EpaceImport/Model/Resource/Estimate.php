<?php

namespace 

class Blackbox_EpaceImport_Model_Resource_Estimate extends Mage_Sales_Model_Resource_Order_Abstract
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix                  = 'epace_estimate_resource';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject                  = 'resource';

    /**
     * Is grid
     *
     * @var boolean
     */
    protected $_grid                         = true;

    /**
     * Use increment id
     *
     * @var boolean
     */
    protected $_useIncrementId               = true;

    /**
     * Entity code for increment id
     *
     * @var string
     */
    protected $_entityCodeForIncrementId     = 'estimate';

    /**
     * Model Initialization
     *
     */
    protected function _construct()
    {
        $this->_init('epacei/estimate', 'entity_id');
    }

    /**
     * Init virtual grid records for entity
     *
     * @return Blackbox_EpaceImport_Model_Resource_Estimate
     */
    protected function _initVirtualGridColumns()
    {
        parent::_initVirtualGridColumns();
        $adapter       = $this->getReadConnection();
        $ifnullFirst   = $adapter->getIfNullSql('{{table}}.firstname', $adapter->quote(''));
        $ifnullMiddle  = $adapter->getIfNullSql('{{table}}.middlename', $adapter->quote(''));
        $ifnullLast    = $adapter->getIfNullSql('{{table}}.lastname', $adapter->quote(''));
        $concatAddress = $adapter->getConcatSql(array(
            $ifnullFirst,
            $adapter->quote(' '),
            $ifnullMiddle,
            $adapter->quote(' '),
            $ifnullLast
        ));

        return $this;
    }

    /**
     * Count existent products of order items by specified product types
     *
     * @param int $orderId
     * @param array $productTypeIds
     * @param bool $isProductTypeIn
     * @return array
     */
    public function aggregateProductsByTypes($orderId, $productTypeIds = array(), $isProductTypeIn = false)
    {
        $adapter = $this->getReadConnection();
        $select  = $adapter->select()
            ->from(
                array('o' => $this->getTable('epacei/estimate_item')),
                array('o.product_type', new Zend_Db_Expr('COUNT(*)')))
            ->joinInner(
                array('p' => $this->getTable('catalog/product')),
                'o.product_id=p.entity_id',
                array())
            ->where('o.estimate_id=?', $orderId)
            ->group('o.product_type')
        ;
        if ($productTypeIds) {
            $select->where(
                sprintf('(o.product_type %s (?))', ($isProductTypeIn ? 'IN' : 'NOT IN')),
                $productTypeIds);
        }
        return $adapter->fetchPairs($select);
    }

    /**
     * Retrieve order_increment_id by order_id
     *
     * @param int $orderId
     * @return string
     */
    public function getIncrementId($orderId)
    {
        $adapter = $this->getReadConnection();
        $bind    = array(':entity_id' => $orderId);
        $select  = $adapter->select()
            ->from($this->getMainTable(), array("increment_id"))
            ->where('entity_id = :entity_id');
        return $adapter->fetchOne($select, $bind);
    }
}
