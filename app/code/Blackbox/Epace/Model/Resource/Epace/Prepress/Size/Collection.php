<?php

/**
 * @method Blackbox_Epace_Model_Epace_Prepress_Size[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_Prepress_Size_Collection
 */
class Blackbox_Epace_Model_Resource_Epace_Prepress_Size_Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/prepress_size');
    }
}