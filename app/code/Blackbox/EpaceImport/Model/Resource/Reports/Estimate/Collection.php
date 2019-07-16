<?php

class Blackbox_EpaceImport_Model_resource_Reports_Estimate_Collection extends Mage_Reports_Model_Resource_Order_Collection
{
    public function calculateMonthlySales($isFilter = 0)
    {
        $adapter = $this->getConnection();

        $this->setMainTable('epacei/estimate');
        $this->removeAllFieldsFromSelect();

        $expr = $this->_getSalesAmountExpression();

//        if ($isFilter == 0) {
//            $expr = '(' . $expr . ') * main_table.base_to_global_rate';
//        }

        $this->getSelect()
            ->columns(array(
                'monthly' => "SUM({$expr})",
                'average'  => "AVG({$expr})"
            ))
            ->where('main_table.created_at > ?', date('Y-m-d', strtotime('-1 month')))
            ->where('status = ?', Blackbox_EpaceImport_Model_Estimate::STATUS_CONVERTED_TO_JOB);

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
                    $adapter->getIfNullSql('main_table.base_grand_total', 0)
                )
            ));

            Mage::dispatchEvent('estimate_prepare_amount_expression', array(
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