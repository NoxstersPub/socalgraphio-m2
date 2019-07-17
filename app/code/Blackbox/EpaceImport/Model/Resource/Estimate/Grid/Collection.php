<?php
namespace Blackbox\EpaceImport\Model\Resource\Estimate\Grid;

/**
 * Flat sales order grid collection
 *
 * @category    Mage
 * @package     \Blackbox\EpaceImport
 * @author      Magento Framework Team <core@magentocommerce.com>
 */
class Collection extends \Blackbox\EpaceImport\Model\Resource\Estimate\Collection
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'epacei_estimate_grid_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject    = 'estimate_grid_collection';

    /**
     * Customer mode flag
     *
     * @var bool
     */
    protected $_customerModeFlag = false;

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setMainTable('epacei/estimate_grid');
    }

    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        if ($this->getIsCustomerMode()) {
            $this->_renderFilters();

            $unionSelect = clone $this->getSelect();

            $unionSelect->reset(Zend_Db_Select::ORDER);
            $unionSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $unionSelect->reset(Zend_Db_Select::LIMIT_OFFSET);

            $countSelect = clone $this->getSelect();
            $countSelect->reset();
            $countSelect->from(array('a' => $unionSelect), 'COUNT(*)');
        } else {
            $countSelect = parent::getSelectCountSql();
        }

        return $countSelect;
    }

    /**
     * Set customer mode flag value
     *
     * @param bool $value
     * @return \Blackbox\EpaceImport\Model\Resource\Estimate\Grid\Collection
     */
    public function setIsCustomerMode($value)
    {
        $this->_customerModeFlag = (bool)$value;
        return $this;
    }

    /**
     * Get customer mode flag value
     *
     * @return bool
     */
    public function getIsCustomerMode()
    {
        return $this->_customerModeFlag;
    }
}
