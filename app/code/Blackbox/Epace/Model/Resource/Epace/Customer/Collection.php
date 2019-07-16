<?php

/**
 * @method Blackbox_Epace_Model_Epace_Customer[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_Customer_Collection
 */
class Blackbox_Epace_Model_Resource_Epace_Customer_Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/customer');
    }
}