<?php

namespace Blackbox\Epace\Model\Epace\Job;

class Note extends \Blackbox\Epace\Model\Epace\Job\AbstractChild
{
    protected function _construct()
    {
        $this->_init('JobNote', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'job' => '',
            'createdBy' => '',
            'createdDate' => 'date',
            'createdTime' => 'date',
            'department' => '',
            'note' => '',
            'fromEstimating' => 'bool',
            'fromQuote' => 'bool',
            'customerViewable' => 'bool',
            'noteSummary' => '',
        ];
    }
}