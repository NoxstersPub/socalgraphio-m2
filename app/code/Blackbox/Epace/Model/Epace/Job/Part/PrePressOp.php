<?php

namespace Blackbox\Epace\Model\Epace\Job\Part;

class PrePressOp extends \Blackbox\Epace\Model\Epace\Job\Part\AbstractChild
{
    protected function _construct()
    {
        $this->_init('JobPartPrePressOp', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'description' => '',
            'quantity' => '',
            'ganged' => '',
            'numOut' => '',
            'unitLabel' => '',
            'prepressItem' => '',
            'sizeWidth' => '',
            'sizeHeight' => '',
            'prepActivity' => '',
            'state' => '',
            'job' => '',
            'jobPart' => '',
            'manual' => 'bool',
            'sequence' => '',
            'customerViewable' => 'bool',
            'size' => '',
            'JobPartKey' => '',
        ];
    }
}