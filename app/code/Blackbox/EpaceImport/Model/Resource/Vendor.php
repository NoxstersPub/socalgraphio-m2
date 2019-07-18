<?php

namespace Blackbox\EpaceImport\Model\Resource;

class Vendor extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb 
{
    protected function _construct()
    {
        $this->_init('epacei/vendor', 'entity_id');
    }
}