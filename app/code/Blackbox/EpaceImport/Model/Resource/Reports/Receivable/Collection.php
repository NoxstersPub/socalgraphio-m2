<?php

class Blackbox_EpaceImport_Model_Resource_Reports_Receivable_Collection extends Blackbox_EpaceImport_Model_Resource_Receivable_Collection
{

    /**
     * Sales amount expression
     *
     * @var string
     */
    protected $_salesAmountExpression;

    public function calculateLast90DaysSales($isFilter = 0)
    {
        $this->setMainTable('epacei/receivable');
        $this->removeAllFieldsFromSelect();

        $expr = $this->_getSalesAmountExpression();

        if ($isFilter == 0) {
            $expr = '(' . $expr . ') * main_table.base_to_global_rate';
        }

        $this->getSelect()
            ->columns(array(
                'sum' => "SUM({$expr})",
                'average'  => "AVG({$expr})"
            ))
            ->where('main_table.created_at > ?', date('Y-m-d', strtotime('-90 days')));

        return $this;
    }

    public function calculate90DaysOutstanding($isFilter = 0)
    {
        $this->setMainTable('epacei/receivable');
        $this->removeAllFieldsFromSelect();

        $expr = 'grand_total';
        if ($isFilter) {
            $expr = '(' . $expr . ') * main_table.base_to_global_rate';
        }

        $this->getSelect()
            ->columns([
                'customer_id',
                'days_outstanding' => 'DATEDIFF(NOW(), created_at)',
                'grand_total' => $expr
            ])
            ->where('state != ?', Blackbox_EpaceImport_Model_Receivable::STATE_CLOSED)
            ->order('days_outstanding DESC');

        return $this;
    }

    /**
     * Get sales amount expression
     *
     * @return string
     */
    protected function _getSalesAmountExpression()
    {
        if (is_null($this->_salesAmountExpression)) {
            $adapter = $this->getConnection();
            $expressionTransferObject = new Varien_Object(array(
                'expression' => '%s',
                'arguments' => array(
                    $adapter->getIfNullSql('main_table.base_grand_total', 0),
                )
            ));

            Mage::dispatchEvent('receivable_prepare_amount_expression', array(
                'collection' => $this,
                'expression_object' => $expressionTransferObject,
            ));
            $this->_salesAmountExpression = vsprintf(
                $expressionTransferObject->getExpression(),
                $expressionTransferObject->getArguments()
            );
        }

        return $this->_salesAmountExpression;
    }
}
