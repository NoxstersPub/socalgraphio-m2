<?php

/**
 * @method Blackbox_Epace_Model_Epace_Job_Type[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_Job_Type_Collection
 */
class Blackbox_Epace_Model_Resource_Epace_Job_Type_Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/job_type');
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