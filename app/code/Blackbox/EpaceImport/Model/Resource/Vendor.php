<?php

class Blackbox_EpaceImport_Model_Resource_Vendor extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('epacei/vendor', 'entity_id');
    }
}