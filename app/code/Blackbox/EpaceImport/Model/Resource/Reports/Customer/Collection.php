<?php

namespace Blackbox\EpaceImport\Model\Resource\Reports\Customer;

class Collection extends \Magento\Customer\Model\ResourceModel\Customer\Collection
{
    public function calculateSalesRepsMonthlySales($limit = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();	
	/** @var \Magento\Framework\Event\ManagerInterface $manager */
        $helper = $objectManager->get('Blackbox\EpaceImport\Helper\EpaceImport');
        $this
            ->addAttributeToFilter('group_id', $helper->getWholesaleCustomerGroupId())
            ->_addAttributeJoin('firstname', 'left')
            ->_addAttributeJoin('middlename', 'left')
            ->_addAttributeJoin('lastname', 'left');

        $customerFieldSql = $this->getConnection()->getConcatSql([
            'at_firstname.value',
            'at_middlename.value',
            'at_lastname.value'
        ], ' ');

        /** @var Mage_Sales_Model_Resource_Order_Collection $orderCollection */
        $orderCollection = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\Collection');
        $select = $orderCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns([
                'sales_person_id',
                'orders_count' => 'count(entity_id)',
                'grand_total' => 'sum(base_grand_total)',
            ])
            ->where('created_at > ?', date('y-m-d', strtotime('-1 month')))
            ->group('sales_person_id');

        /** @var Blackbox_EpaceImport_Model_Resource_Estimate_Collection $estimateCollection */
        $estimateCollection = $objectManager->get('Blackbox\EpaceImport\Model\Resource\Estimate\Collection');
        $select = $estimateCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns([
                'sales_person_id',
                'estimates' => 'sum(base_grand_total)',
                'estimates_cost' => 'sum(base_total_cost)'
            ])
            ->where('created_at > ?', date('y-m-d', strtotime('-1 month')))
            ->where('status = ?', \Blackbox\EpaceImport\Model\Estimate::STATUS_CONVERTED_TO_JOB)
            ->group('sales_person_id');

        $this->getSelect()
            ->joinLeft(['orders' => $orderCollection->getSelect()], 'orders.sales_person_id = e.entity_id', ['orders_count', 'grand_total'])
            ->joinLeft(['estimates' => $estimateCollection->getSelect()], 'estimates.sales_person_id = e.entity_id', ['estimates', 'estimates_cost'])
            ->columns([
                'customer' => $customerFieldSql,
            ]);
        if ($limit) {
            $this->getSelect()->limit($limit);
        }
        $this->getSelect()->order('grand_total DESC');

        return $this;
    }

    public function calculateCustomerSales($limit = null)
    {
        $this->_addAttributeJoin('firstname', 'left')
            ->_addAttributeJoin('middlename', 'left')
            ->_addAttributeJoin('lastname', 'left');

        $customerFieldSql = $this->getConnection()->getConcatSql([
            'at_firstname.value',
            'at_middlename.value',
            'at_lastname.value'
        ], ' ');

        $estimatesSelect = $this->getConnection()->select()
            ->from($this->getTable('epacei/estimate'), ['customer_id', 'estimates' => 'sum(base_grand_total)'])
            ->where('status = ?', \Blackbox\EpaceImport\Model\Estimate::STATUS_CONVERTED_TO_JOB)
            ->group('customer_id');
        $ordersSelect = $this->getConnection()->select()->from($this->getTable('sales/order'), ['customer_id', 'orders' => 'sum(base_grand_total)', 'billed' => 'sum(total_paid)'])->group('customer_id');

        $this->getSelect()->joinLeft(['estimates' => $estimatesSelect], 'estimates.customer_id = e.entity_id', ['estimates'])
            ->joinLeft(['orders' => $ordersSelect], 'orders.customer_id = e.entity_id', ['orders', 'billed'])
            ->group('e.entity_id')
            ->columns([
                'estimates.estimates',
                'orders.orders',
                'orders.billed',
                'customer' => $customerFieldSql
            ])
            ->order('billed DESC');

        return $this;
    }
}