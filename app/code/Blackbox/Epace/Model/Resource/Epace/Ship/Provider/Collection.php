<?php

/**
 * @method Blackbox_Epace_Model_Epace_Ship_Provider[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_Ship_Provider_Collection
 */
class Blackbox_Epace_Model_Resource_Epace_Ship_Provider_Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/ship_provider');
    }
}