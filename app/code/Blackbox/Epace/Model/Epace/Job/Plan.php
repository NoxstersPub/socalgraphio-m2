<?php

namespace Blackbox\Epace\Model\Epace\Job;

class Plan extends \Blackbox\Epace\Model\Epace\Job\Part\AbstractChild
{
    const STATUS_JOB_ON_HOLD = 6;
    const STATUS_MACHINE_BREAKDOWN = 7;
    const STATUS_MAKEREADY = 2;
    const STATUS_NOT_READY = 0;
    const STATUS_PROCESS_COMPLETE = 8;
    const STATUS_PROCESS_READY = 1;
    const STATUS_PROCESS_STOP = 5;
    const STATUS_RUN_WORKING_ON = 3;
    const STATUS_WASHUP = 4;

    protected function _construct()
    {
        $this->_init('JobPlan', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'job' => '',
            'part' => '',
            'activityCode' => '',
            'activityCodeForced' => 'bool',
            'status' => 'int',
            'startDate' => 'date',
            'endDate' => 'date',
            'startTime' => 'date',
            'endTime' => 'date',
            'priority' => '',
            'manual' => 'bool',
            'plannedHours' => '',
            'estimatedHours' => '',
            'estimatedProductionUnits' => '',
            'fromJobType' => 'bool',
            'onScheduleBoard' => 'bool',
            'jobPartPressForm' => '',
            'printFlowForm' => '',
            'setupHours' => '',
            'previousTask' => '',
            'nextTask' => '',
            'leadTime' => 'float',
            'lagTime' => 'float',
            'submittedToDevice' => 'bool',
            'deviceComplete' => 'bool',
            'run' => '',
            'estimateSource' => '',
            'quoteSource' => '',
            'internalSplitSource' => '',
            'taskLocked' => 'bool',
            'earliestDateTimeIncrementCounter' => 'int',
            'active' => 'bool',
            'plantManagerSynchronized' => 'bool',
            'editable' => 'bool',
            'actualHours' => '',
            'remainingHours' => '',
            'runHours' => '',
            'JobPartKey' => '',
        ];
    }
}