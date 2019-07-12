<?php

namespace \Blackbox\Epace\Model\Resource\Epace\CSR;

class Collection extends Blackbox_Epace_Model_Resource_Epace_Collection
{
    protected function _construct()
    {
        $this->_init('efi/cSR');
    }
}