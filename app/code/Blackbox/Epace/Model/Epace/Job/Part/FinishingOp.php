<?php

namespace Blackbox\Epace\Model\Epace\Job_Part;

class FinishingOp extends \Blackbox\Epace\Model\Epace\Job\Part\AbstractChild
{
    protected function _construct()
    {
        $this->_init('JobPartFinishingOp', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'quantity' => '',
            'finishingOperation' => '',
            'units' => '',
            'unitLabel' => '',
            'makeReadyHours' => '',
            'hours' => '',
            'state' => '',
            'numberHelpers' => '',
            'numUp' => '',
            'numPasses' => '',
            'job' => '',
            'jobPart' => '',
            'manual' => 'bool',
            'sequence' => '',
            'runSpeed' => '',
            'jdfSubmitted' => 'bool',
            'inLine' => 'bool',
            'autoChange' => 'bool',
            'estimateSource' => '',
            'rotateStack' => 'bool',
            'active' => 'bool',
            'qtyPerUnit' => '',
            'reviewPrompt' => 'bool',
            'createdFromSplit' => 'bool',
            'customerViewable' => 'bool',
            'numberPerfs' => '',
            'numberScores' => '',
            'numberSlits' => '',
            'numberGlues' => '',
            'runSpoilage' => '',
            'makeReadySpoilage' => '',
            'quantityIn' => '',
            'followingOperationSpoilage' => '',
            'JobPartKey' => '',
        ];
    }
}