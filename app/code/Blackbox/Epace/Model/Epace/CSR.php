<?php

namespace Blackbox\Epace\Model\Epace;

class CSR extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('CSR', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'name' => 'string',
            'email' => 'string',
            'notes' => 'string',
            'active' => 'bool',
            'phoneNumber' => 'string',
        ];
    }
}
