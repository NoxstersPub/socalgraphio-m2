<?php

/**
 * @method Blackbox_Epace_Model_Epace_Carton_Content[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_Carton_Content_Collection
 */
class Blackbox_Epace_Model_Resource_Epace_Carton_Content_Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/carton_content');
    }
}