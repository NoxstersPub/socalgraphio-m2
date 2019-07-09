<?php

namespace Blackbox\Epace\Model\Epace\Job\Part;

class OutsidePurch extends \Blackbox\Epace\Model\Epace\Job\Part\AbstractChild
{
    protected function _construct()
    {
        $this->_init('JobPartOutsidePurch', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'job' => '',
            'jobPart' => '',
            'quantity' => '',
            'setupCost' => '',
            'totalCost' => '',
            'outsidePurchaseMarkup' => '',
            'outsidePurchaseSetupMarkupForced' => 'bool',
            'quoteNum' => '',
            'description' => '',
            'vendor' => '',
            'used' => 'bool',
            'activityCode' => '',
            'reviewedForPO' => 'bool',
            'manual' => 'bool',
            'estimateSource' => '',
            'purchasedQuantity' => '',
            'altCurrency' => '',
            'altCurrencyRate' => '',
            'altCurrencyRateSource' => '',
            'altCurrencyRateSourceNote' => 'string',
            'unitPrice' => '',
            'uom' => '',
            'mWeight' => '',
            'setupActivityCode' => '',
            'JobPartKey' => '',
        ];
    }
}