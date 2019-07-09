<?php

/**
 * @method Blackbox_Epace_Model_Epace_State[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_State_Collection
 */
class Blackbox_Epace_Model_Resource_Epace_State_Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/state');
    }
}