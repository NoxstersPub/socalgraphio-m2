<?php
/**
 * Flat sales order status history collection
 *
 * @category    Mage
 * @package     Blackbox_EpaceImport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Blackbox_EpaceImport_Model_Resource_Estimate_Status_Collection extends Blackbox_Epace_Model_Resource_Epace_Estimate_Status_Collection
{
    /**
     * Define label order
     *
     * @param string $dir
     * @return Blackbox_EpaceImport_Model_Resource_Estimate_Status_Collection
     */
    public function orderByLabel($dir = 'ASC')
    {
        $this->setOrder('description', $dir);
        return $this;
    }
}
