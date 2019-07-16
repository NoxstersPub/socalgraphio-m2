<?php
namespace Blackbox\Epace\Model\Epace;

class Proof extends \Blackbox\Epace\Model\Epace\Job\Part\AbstractChild 
{
    protected function _construct()
    {
        $this->_init('Proof', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'job' => 'string',
            'jobPart' => 'string',
            'description' => 'string',
            'status' => 'int',
            'notes' => 'string',
            'requestedBy' => 'string',
            'jobPartKey' => 'string',
        ];
    }
}