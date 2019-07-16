<?php

namespace \Blackbox\Epace\Model\Resource\Epace\Estimate;

/**
 * @method Blackbox_Epace_Model_Epace_Estimate[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_Estimate_Collection
 */

class Collection extends \Blackbox\Epace\Model\Resource\Epace\Collection
{
    protected function _construct()
    {
        $this->_init('Blackbox\Epace\Model\Epace\Estimate');
    }
}