<?php

/**
 * @method Blackbox_Epace_Model_Epace_SalesCategory[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_SalesCategory_Collection
 */
class Blackbox_Epace_Model_Resource_Epace_SalesCategory_Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/salesCategory');
    }
}