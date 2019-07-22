<?php

namespace Blackbox\EpaceImport\Model\Resource;

class Receivable extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb 
{
    protected function _construct()
    {
        $this->_init('epacei/receivable', 'entity_id');
    }
}