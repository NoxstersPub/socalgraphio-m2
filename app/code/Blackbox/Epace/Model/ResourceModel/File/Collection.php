<?php

namespace Blackbox\Epace\Model\ResourceModel\File;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Blackbox\Epace\Model\File', 'Blackbox\Epace\Model\ResourceModel\File');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>