<?php

namespace Blackbox\Epace\Model\Epace\Change;

class Order extends \Blackbox\Epace\Model\Epace\Job\Part\AbstractChild
{
    protected function _construct()
    {
        $this->_init('ChangeOrder', 'id');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Change_Order_Type|bool
     */
    public function getType()
    {
        return $this->_getObject('type', 'type', 'efi/change_order_type', true);
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Change_Order_Type $type
     * @return $this
     */
    public function setType(\Blackbox\Epace\Model\Epace\Change\Order\Type $type)
    {
        return $this->_setObject('type', $type);
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'job' => 'string',
            'jobPart' => 'string',
            'department' => 'int',
            'num' => 'int',
            'entryDate' => 'date',
            'entryTime' => 'date',
            'description' => 'string',
            'enteredBy' => 'string',
            'type' => 'int',
            'billed' => 'bool',
            'taxAmount' => 'float',
            'taxBase' => 'float',
            'JobPartKey' => 'string',
        ];
    }
}