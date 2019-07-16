<?php

class Blackbox_EpaceImport_Model_Sales_Order_Shipment extends Blackbox_Notification_Model_Sales_Order_Shipment//Mage_Sales_Model_Order_Shipment
{
    public function dispose()
    {
        $this->_order = null;
        $this->_items = null;
        $this->_tracks = null;
        $this->_comments = null;
        $this->unsetData();
        $this->unsetOldData();
    }
}