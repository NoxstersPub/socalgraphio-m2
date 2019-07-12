<?php

namespace \Blackbox\Epace\Model\Resource\Epace\Estimate\Status;

class Collection extends \Blackbox\Epace\Model\Resource\Epace\Collection
{
    protected function _construct()
    {
        $this->_init('Blackbox\Epace\Model\Epace\Estimate\Status');
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray('id', 'description');
    }

    public function toOptionHash()
    {
        return $this->_toOptionHash('id', 'description');
    }
}