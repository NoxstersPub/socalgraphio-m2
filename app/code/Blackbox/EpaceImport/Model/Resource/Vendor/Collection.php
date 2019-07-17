<?php

namespace Blackbox\EpaceImport\Model\Resource\Vendor;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection
{
    protected function _construct()
    {
        $this->_init('epacei/vendor');
    }
}