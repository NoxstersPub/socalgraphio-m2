<?php
namespace Blackbox\Epace\Model\ResourceModel;

class File extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('epace_event_file', 'id');
    }
}
?>