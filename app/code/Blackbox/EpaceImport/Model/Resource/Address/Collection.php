<?php

namespace Blackbox\EpaceImport\Model\Resource\Address;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection\AbstractCollection
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix    = 'epacei_address_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject    = 'epacei_address_collection';

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('epacei/address');
    }

    /**
     * Redeclare after load method for dispatch event
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();	
	/** @var \Magento\Framework\Event\ManagerInterface $manager */
	$manager = $objectManager->get('Magento\Framework\Event\ManagerInterface');
	$manager->dispatch($this->_eventPrefix . '_load_after', array(
            $this->_eventObject => $this
        ));

        return $this;
    }
}
