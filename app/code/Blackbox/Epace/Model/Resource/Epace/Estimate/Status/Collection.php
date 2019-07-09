<?php

/**
 * @method Blackbox_Epace_Model_Epace_Estimate_Status[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_Estimate_Status_Collection
 */
class Blackbox_Epace_Model_Resource_Epace_Estimate_Status_Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/estimate_status');
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray('id', 'description');
    }

    public function toOptionHash()
    {
        return $this->_toOptionHash('id', 'description');
    }
}