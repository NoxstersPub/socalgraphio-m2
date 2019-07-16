<?php

namespace Blackbox\Epace\Model\Epace;

class Currency extends \Blackbox\Epace\Model\Epace\EpaceObject

{
    protected function _construct()
    {
        $this->_init('Currency', 'isoCurrencyId');
    }

    public function getDefinition()
    {
        return [
            'isoCurrencyId' => 'string',
            'displayCode' => 'string',
            'displayCurrencyCode' => 'bool',
            'description' => 'string',
            'active' => 'bool',
            'sequence' => 'int',
            'symbol' => 'string',
            'decimalSeperator' => 'string',
            'groupSeperator' => 'string',
            'patternSeperator' => 'string',
            'groupSize' => 'int',
            'zeroDigit' => 'int',
            'digit' => 'string',
            'symbolLocation' => 'int',
            'conversion' => 'string',
            'pattern' => 'string',
            'sample' => 'string',
            'currentExchangeRate' => 'float',
        ];
    }
}