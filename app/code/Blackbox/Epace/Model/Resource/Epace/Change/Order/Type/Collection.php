<?php

/**
 * @method Blackbox_Epace_Model_Epace_Change_Order_Type[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_Change_Order_Type_Collection
 */
class Blackbox_Epace_Model_Resource_Epace_Change_Order_Type_Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/change_order_type');
    }
}