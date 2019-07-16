<?php

namespace Blackbox\Epace\Model\ResourceModel\Event;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Blackbox\Epace\Model\Event', 'Blackbox\Epace\Model\ResourceModel\Event');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>