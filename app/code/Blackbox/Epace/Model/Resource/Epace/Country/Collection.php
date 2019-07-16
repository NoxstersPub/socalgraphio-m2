<?php

/**
 * @method Blackbox_Epace_Model_Epace_Country[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_Country_Collection
 */
class Blackbox_Epace_Model_Resource_Epace_Country_Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/country');
    }
}