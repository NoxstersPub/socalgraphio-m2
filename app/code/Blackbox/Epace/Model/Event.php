<?php
namespace Blackbox\Epace\Model;

class Event extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    const STATUS_SUCCESS = 'Success';
    const STATUS_WITH_ERRORS = 'With errors';
    const STATUS_CRITICAL = 'Critical';


    protected function _construct()
    {
        $this->_init('Blackbox\Epace\Model\ResourceModel\Event');
    }
    
}
?>