<?php

namespace Blackbox\Epace\Model\Resource\Epace\Job_Material;

class Collection extends \Blackbox\Epace\Model\Resource\Epace\Collection
{
    protected function _construct()
    {
        $this->_init('Blackbox\Epace\Model\Epace\Job\Material');
    }
}