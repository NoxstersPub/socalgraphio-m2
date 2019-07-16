<?php
namespace Blackbox\Epace\Model\Epace;

trait PersonsTrait
{
    /**
     * @return \Blackbox\Epace\Model\Customer|bool
     */
    public function getCustomer()
    {
        return $this->_getObject('customer', 'customer', 'efi/customer', true);
    }

    /**
     * @param \Blackbox\Epace\Model\Customer $customer
     * @return $this
     */
    public function setCustomer(\Blackbox\Epace\Model\Epace\Customer $customer)
    {
        return $this->_setObject('customer', $customer);
    }

    /**
     * @return \Blackbox\Epace\Model\SalesPerson|bool
     */
    public function getSalesPerson()
    {
        return $this->_getObject('salesPerson', 'salesPerson', 'efi/salesPerson', true);
    }

    /**
     * @param \Blackbox\Epace\Model\SalesPerson $salesPerson
     * @return $this
     */
    public function setSalesPerson(\Blackbox\Epace\Model\Epace\SalesPerson $salesPerson)
    {
        return $this->_setObject('salesPerson', $salesPerson);
    }

    /**
     * @return \Blackbox\Epace\Model\CSR|bool
     */
    public function getCSR()
    {
        return $this->_getObject('csr', 'csr', 'efi/cSR', true);
    }

    /**
     * @param \Blackbox\Epace\Model\CSR $csr
     * @return $this
     */
    public function setCSR(\Blackbox\Epace\Model\Epace\CSR $csr)
    {
        return $this->_setObject('csr', $csr);
    }
}