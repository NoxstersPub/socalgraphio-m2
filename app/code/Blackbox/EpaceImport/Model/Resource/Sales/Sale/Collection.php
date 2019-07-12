<?php

class Blackbox_EpaceImport_Model_Resource_Sales_Sale_Collection extends Mage_Sales_Model_Resource_Sale_Collection
{
    /**
     * Customer model
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_salesPerson;

    /**
     * Customer model
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_csr;

    /**
     * Set filter by sales person
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return $this
     */
    public function setSalesPersonFilter(Mage_Customer_Model_Customer $customer)
    {
        $this->_salesPerson = $customer;
        return $this;
    }

    /**
     * Set filter by sales person
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return $this
     */
    public function setCSRFilter(Mage_CUstomer_Model_Customer $customer)
    {
        $this->_csr = $customer;
        return $this;
    }

    /**
     * Before load action
     *
     * @return Varien_Data_Collection_Db
     */
    protected function _beforeLoad()
    {
        $this->getSelect()
            ->from(
                array('sales' => Mage::getResourceSingleton('sales/order')->getMainTable()),
                array(
                    'store_id',
                    'lifetime'      => new Zend_Db_Expr('SUM(sales.base_grand_total)'),
                    'base_lifetime' => new Zend_Db_Expr('SUM(sales.base_grand_total * sales.base_to_global_rate)'),
                    'avgsale'       => new Zend_Db_Expr('AVG(sales.base_grand_total)'),
                    'base_avgsale'  => new Zend_Db_Expr('AVG(sales.base_grand_total * sales.base_to_global_rate)'),
                    'num_orders'    => new Zend_Db_Expr('COUNT(sales.base_grand_total)')
                )
            )
            ->group('sales.store_id');

        $filterField = [];
        $filterValue = [];

        if ($this->_customer instanceof Mage_Customer_Model_Customer) {
            $filterField[] = 'sales.customer_id';
            $filterValue[] = $this->_customer->getId();
        }
        if ($this->_salesPerson instanceof Mage_Customer_Model_Customer) {
            $filterField[] = 'sales.sales_person_id';
            $filterValue[] = $this->_salesPerson->getId();
        }
        if ($this->_csr instanceof Mage_Customer_Model_Customer) {
            $filterField[] = 'sales.csr_id';
            $filterValue[] = $this->_csr->getId();
        }

        if (!empty($filterField) && !empty($filterValue)) {
            if (count($filterField) == 1) {
                $filterField = current($filterField);
            }
            if (count($filterValue) == 1) {
                $filterValue = current($filterValue);
            }

            $this->addFieldToFilter($filterField, $filterValue);
        }

        if (!is_null($this->_orderStateValue)) {
            $condition = '';
            switch ($this->_orderStateCondition) {
                case 'IN' :
                    $condition = 'in';
                    break;
                case 'NOT IN' :
                    $condition = 'nin';
                    break;
            }
            $this->addFieldToFilter('state', array($condition => $this->_orderStateValue));
        }

        Mage::dispatchEvent('sales_sale_collection_query_before', array('collection' => $this));
        return $this;
    }
}