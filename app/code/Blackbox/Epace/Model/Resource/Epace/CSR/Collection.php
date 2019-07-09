<?php

/**
 * @method Blackbox_Epace_Model_Epace_CSR[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_CSR_Collection
 */
class Blackbox_Epace_Model_Resource_Epace_CSR_Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/cSR');
    }
}