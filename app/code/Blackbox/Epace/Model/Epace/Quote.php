<?php

namespace Blackbox\Epace\Model\Epace;

class Quote extends \Blackbox\Epace\Model\Epace\AbstractObject
{
    protected function _construct()
    {
        $this->_init('Quote', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'customer' => 'string',
            'customerName' => '',
            'address1' => '',
            'address2' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'country' => '',
            'contactId' => '',
            'priceList' => '',
            'quoteNumber' => '',
            'deliveryDate' => 'date',
            'jobConvertedTo' => '',
            'jobType' => '',
            'requestDate' => 'date',
            'salesperson' => '',
            'description' => '',
            'isTemplate' => '',
            'status' => 'int',
            'eachOf' => '',
            'quoteAction' => '',
            'enteredBy' => '',
            'suspendCalculations' => '',
            'addCRMOpportunity' => '',
            'addCRMActivity' => '',
            'taxableCode' => '',
            'salesTax' => '',
            'shipToContact' => '',
            'billToContact' => '',
            'comboPrice' => '',
            'altCurrency' => '',
            'altCurrencyRate' => '',
            'quantity' => '',
            'quantityRemaining' => '',
            'updateJobInfoOnConvert' => '',
            'nextSequence' => '',
            'stateKey' => '',
        ];
    }
}