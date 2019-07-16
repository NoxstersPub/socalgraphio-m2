<?php

/**
 * @method Blackbox_Epace_Model_Epace_POStatus[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_POStatus_Collection
 */
class Blackbox_Epace_Model_Resource_Epace_POStatus_Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/pOStatus');
    }
}