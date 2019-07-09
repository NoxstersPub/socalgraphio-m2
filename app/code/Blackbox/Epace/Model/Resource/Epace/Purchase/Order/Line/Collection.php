<?php

/**
 * @method Blackbox_Epace_Model_Epace_Purchase_Order_Line[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_Purchase_Order_Line_Collection
 */
class Blackbox_Epace_Model_Resource_Epace_Purchase_Order_Line_Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/purchase_order_line');
    }
}