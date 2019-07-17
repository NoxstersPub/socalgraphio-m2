<?php

namespace Blackbox\EpaceImport\Model\Resource\Estimate\Collection;

abstract class EstimateAbstract extends \Magento\Sales\Model\ResourceModel\Order\Collection\AbstractCollection
{
    /**
     * Estimate object
     *
     * @var Blackbox_EpaceImport_Model_Estimate
     */
    protected $_estimate   = null;

    /**
     * Estimate field for setEstimateFilter
     *
     * @var string
     */
    protected $_estimateField   = 'parent_id';

    /**
     * Set sales estimate model as parent collection object
     *
     * @param Blackbox_EpaceImport_Model_Estimate $estimate
     * @return Blackbox_EpaceImport_Model_Resource_Estimate_Collection_Abstract
     */
    public function setEstimate($estimate)
    {
        $this->_estimate = $estimate;
        if ($this->_eventPrefix && $this->_eventObject) {
            $manager = $objectManager->get('Magento\Framework\Event\ManagerInterface');
            $manager->dispatch($this->_eventPrefix . '_set_estimate', array(
                'collection' => $this,
                $this->_eventObject => $this,
                'estimate' => $estimate
            ));
        }

        return $this;
    }

    /**
     * Retrieve sales estimate as parent collection object
     *
     * @return Blackbox_EpaceImport_Model_Estimate|null
     */
    public function getEstimate()
    {
        return $this->_estimate;
    }

    /**
     * Add estimate filter
     *
     * @param int|Blackbox_EpaceImport_Model_Estimate $estimate
     * @return Blackbox_EpaceImport_Model_Resource_Estimate_Collection_Abstract
     */
    public function setEstimateFilter($estimate)
    {
        if ($estimate instanceof \Blackbox\EpaceImport\Model\Estimate) {
            $this->setEstimate($estimate);
            $estimateId = $estimate->getId();
            if ($estimateId) {
                $this->addFieldToFilter($this->_estimateField, $estimateId);
            } else {
                $this->_totalRecords = 0;
                $this->_setIsLoaded(true);
            }
        } else {
            $this->addFieldToFilter($this->_estimateField, $estimate);
        }
        return $this;
    }
}
