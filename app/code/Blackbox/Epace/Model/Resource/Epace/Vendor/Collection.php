<?php

/**
 * @method Blackbox_Epace_Model_Epace_Vendor[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_Vendor_Collection
 */
class Blackbox_Epace_Model_Resource_Epace_Vendor_Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/vendor');
    }
}