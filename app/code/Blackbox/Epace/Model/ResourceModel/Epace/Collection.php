<?php

namespace Blackbox\Epace\Model\ResourceModel\Epace;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Blackbox\Epace\Model\Epace', 'Blackbox\Epace\Model\ResourceModel\Epace');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>