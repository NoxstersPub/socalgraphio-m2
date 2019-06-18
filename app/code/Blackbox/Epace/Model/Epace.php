<?php
namespace Blackbox\Epace\Model;

class Epace extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Blackbox\Epace\Model\ResourceModel\Epace');
    }
}
?>