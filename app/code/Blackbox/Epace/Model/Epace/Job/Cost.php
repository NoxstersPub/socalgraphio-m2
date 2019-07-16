<?php

namespace Blackbox\Epace\Model\Epace\Job;

class Cost extends \Blackbox\Epace\Model\Epace\Job\Part\AbstractChild
{
    protected function _construct()
    {
        $this->_init('JobCost', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'job' => '',
            'jobPart' => '',
            'activityCode' => '',
            'overlap' => 'bool',
            'chargeClass' => '',
            'startDate' => 'date',
            'journalCode' => '',
            'sourceID' => '',
            'startTime' => 'date',
            'estimatedHours' => '',
            'estimatedProdUnits' => '',
            'estimatedCost' => '',
            'estimatedSell' => '',
            'estimate' => 'string',
            'estimatePart' => 'string',
            'editFlag' => 'bool',
            'endDate' => 'date',
            'transactionType' => '',
            'approved' => 'bool',
            'billRate' => '',
            'postable' => 'bool',
            'jobPlan' => '',
            'autoPost' => 'bool',
            'postingStatus' => '',
            'includeInAdditionalPerM' => 'bool',
            'negated' => 'bool',
            'estimateSource' => 'link',
            'quickEntry' => 'bool',
            'failedAutoPost' => 'bool',
            'posted' => 'bool',
            'overrideJobStatus' => 'bool',
            'hours' => '',
            'cost' => '',
            'prodUnits' => '',
            'closed' => 'bool',
            'pause' => 'bool',
            'closeActivity' => 'bool',
            'totalEstimatedHours' => '',
            'inWIP' => 'bool',
            'postageUsed' => '',
            'countDifference' => '',
            'JobPartKey' => '',
        ];
    }
}