<?php

class Blackbox_EpaceImport_Model_resource_Reports_Order_Collection extends Blackbox_CinemaCloud_Model_Resource_Reports_Order_Collection
{
    protected $categories = false;

    public function calculateMonthlySales($isFilter = 0)
    {
        $statuses = Mage::getSingleton('sales/config')
            ->getOrderStatusesForState(Mage_Sales_Model_Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = array(0);
        }
        $adapter = $this->getConnection();

        if (Mage::getStoreConfig('sales/dashboard/use_aggregated_data')) {
            $this->setMainTable('sales/order_aggregated_created');
            $this->removeAllFieldsFromSelect();
            $averageExpr = $adapter->getCheckSql(
                'SUM(main_table.orders_count) > 0',
                'SUM(main_table.total_revenue_amount)/SUM(main_table.orders_count)',
                0);
            $this->getSelect()->columns(array(
                'monthly' => 'SUM(main_table.total_revenue_amount)',
                'average'  => $averageExpr
            ));

            if (!$isFilter) {
                $this->addFieldToFilter('store_id',
                    array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
                );
            }
            $this->getSelect()->where('main_table.order_status NOT IN(?)', $statuses)
                ->where('main_table.period >= ?', date('Y-m-d', strtotime('-1 month')));
        } else {
            $this->setMainTable('sales/order');
            $this->removeAllFieldsFromSelect();

            $expr = $this->_getSalesAmountExpression();

            if ($isFilter == 0) {
                $expr = '(' . $expr . ') * main_table.base_to_global_rate';
            }

            $this->getSelect()
                ->columns(array(
                    'monthly' => "SUM({$expr})",
                    'average'  => "AVG({$expr})"
                ))
                ->where('main_table.status NOT IN(?)', $statuses)
                ->where('main_table.state NOT IN(?)', array(
                        Mage_Sales_Model_Order::STATE_NEW,
                        Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
                )
                ->where('main_table.created_at > ?', date('Y-m-d', strtotime('-1 month')));
        }
        return $this;
    }

    public function calculateProfitPerJob($isFilter = 0)
    {
        $this->setMainTable('sales/order');
        $this->removeAllFieldsFromSelect();

        $expr = $this->_getSalesProfitExpression();

        if ($isFilter == 0) {
            $expr = '(' . $expr . ') * main_table.base_to_global_rate';
        }

        $this->getSelect()
            ->columns(array(
                'lifetime' => "SUM({$expr})",
                'average'  => "AVG({$expr})"
            ))
            ->where('job_value IS NOT NULL');

        return $this;
    }

    public function calculateCategoryBreakdown($isFilter = 0)
    {
        $this->categories = true;

        $this->setMainTable('sales/order');
        $this->removeAllFieldsFromSelect();

        $expr = $this->_getSalesAmountExpression();

        if ($isFilter == 0) {
            $expr = '(' . $expr . ') * main_table.base_to_global_rate';
        }

        $categoryExpr = $this->_getCategoryExpression();

        $this->getSelect()
            ->columns([
                'job_type',
                'total' => "SUM({$expr})",
                'count' => 'COUNT(entity_id)'
            ])
            ->where('job_type is not null')
            ->group('job_type')
            ->order('total DESC');

        return $this;
    }

    protected function _calculateTotalsLive($isFilter = 0)
    {
        parent::_calculateTotalsLive($isFilter);

        $adapter = $this->getConnection();

        $estimatePriceExp = $adapter->getIfNullSql('main_table.job_value', 0);;
        $amountToInvoiceExp = $adapter->getIfNullSql('main_table.amount_to_invoice', 0);;

        if ($isFilter == 0) {
            $rateExp = $adapter->getIfNullSql('main_table.base_to_global_rate', 0);
            $this->getSelect()->columns(
                array(
                    'estimate_price'  => new Zend_Db_Expr(sprintf('SUM((%s) * %s)', $estimatePriceExp, $rateExp)),
                    'amount_to_invoice'      => new Zend_Db_Expr(sprintf('SUM((%s) * %s)', $amountToInvoiceExp, $rateExp)),
                )
            );
        } else {
            $this->getSelect()->columns(
                array(
                    'estimate_price'  => new Zend_Db_Expr(sprintf('SUM(%s)', $estimatePriceExp)),
                    'amount_to_invoice'      => new Zend_Db_Expr(sprintf('SUM(%s)', $amountToInvoiceExp)),
                )
            );
        }

        return $this;
    }

    protected function _calculateTotalsAggregated($isFilter = 0)
    {
        return parent::_calculateTotalsAggregated($isFilter);
    }

    protected function _getSalesProfitExpression()
    {
        $adapter = $this->getConnection();
        return $adapter->getIfNullSql('main_table.job_value', 0) . ' - ' . $adapter->getIfNullSql('main_table.subtotal', 0);
    }

    protected function _getCategoryExpression()
    {
        $types = Mage::helper('epacei')->getJobTypes();

        $sql = 'case';
        foreach ($types as $id => $name) {
            $sql .= ' when job_type = ' . $id . ' then \'' . $name . '\'';
        }
        $sql .= ' end';

        return new Zend_Db_Expr($sql);
    }
}