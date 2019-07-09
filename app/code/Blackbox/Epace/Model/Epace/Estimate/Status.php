<?php

namespace Blackbox\Epace\Model\Epace\Estimate; 

class Status extends \Blackbox\Epace\Model\Epace\AbstractObject
{
    const STATUS_OPEN = 1;
    const STATUS_NEED_INFO = 4;
    const STATUS_PRICE_COMPLETE = 5;
    const STATUS_CUSTOMER_SUBMITTED = 3;
    const STATUS_RE_QUOTE = 7;
    const STATUS_CONVERTED_TO_JOB = 2;
    const STATUS_CANCELLED = 6;


    protected function _construct()
    {
        $this->_init('EstimateStatus', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'description' => '',
            'sequence' => '',
            'open' => 'bool',
            'editable' => 'bool',
            'deletable' => 'bool',
            'convertible' => 'bool',
            'triggerCRMActivity' => 'bool',
            'triggerCRMOpportunity' => 'bool',
            'triggerEstimateRequestStatusChange' => '',
            'active' => 'bool',
        ];
    }
}