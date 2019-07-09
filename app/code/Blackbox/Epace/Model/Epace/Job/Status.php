<?php

namespace Blackbox\Epace\Model\Epace\Job;

class Status extends \Blackbox\Epace\Model\Epace\AbstractObject
{
    const STATUS_AUTO_BILLING_OK = 'A';
    const STATUS_CLOSED = 'C';
    const STATUS_COST_TRANSFER = 'T';
    const STATUS_CREDIT_HOLD = '$';
    const STATUS_JOB_CANCELLED = 'X';
    const STATUS_OPEN = 'O';
    const STATUS_PARTL_BILL = 'c';
    const STATUS_SEND_TO_PRINERGY = '!';
    const STATUS_SHIPPED = 'S';
    const STATUS_TO_PLANTMANAGER = 'Z';

    const IN_PRODUCTION_HOLD = 'Hold';
    const IN_PRODUCTION_NO = 'No';
    const IN_PRODUCTION_YES = 'Yes';
    const IN_PRODUCTION_YES_CHANGES = 'Yes-Changes';

    const ASK_NO = 'No';
    const ASK_REQUIRED = 'Required';
    const ASK_YES = 'Yes';

    protected function _construct()
    {
        $this->_init('JobStatus', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'string',
            'description' => 'string',
            'admin' => 'bool',
            'production' => 'bool',
            'adminToProduction' => 'bool',
            'openJob' => 'bool',
            'schedOK' => 'bool',
            'autoCountOK' => 'bool',
            'editable' => 'bool',
            'autoChangable' => 'bool',
            'billingOK' => 'bool',
            'jobChargesOK' => 'bool',
            'printableCancelled' => 'bool',
            'pageflexComplete' => 'bool',
            'active' => 'bool',
            'dsfCompleted' => 'bool',
            'dsfCancelled' => 'bool',
            'triggerJDFConnect' => 'bool',
            'okToFCMakeReadyExport' => 'bool',
            'inProduction' => '',
            'updateJobVersion' => 'bool',
            'externalStatus' => '',
            'askComments' => '',
            'askReason' => '',
            'readyForDeviceSubmission' => 'bool',
            'inTransit' => 'bool',
        ];
    }
}