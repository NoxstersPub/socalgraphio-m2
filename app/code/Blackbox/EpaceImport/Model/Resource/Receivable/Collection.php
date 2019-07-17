<?php

namespace Blackbox\EpaceImport\Model\Resource\Receivable;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('epacei/receivable');
    }
}