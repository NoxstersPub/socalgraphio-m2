<?php

/**
 * @method Blackbox_Epace_Model_Epace_Job_Material[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_Job_Material_Collection
 */
class Blackbox_Epace_Model_Resource_Epace_Job_Material_Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/job_material');
    }
}