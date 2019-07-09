<?php
namespace Blackbox\Epace\Model\Epace;

class Country extends \Blackbox\Epace\Model\Epace\AbstractObject
{
    protected function _construct()
    {
        $this->_init('Country', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => '',
            'isoCountry' => '',
            'isoCountryAlpha3' => '',
            'name' => '',
            'dateFormatPattern' => '',
            'timeFormatPattern' => '',
            'numericFormatPattern' => '',
            'active' => 'bool',
            'stateRequired' => 'bool',
            'sequence' => '',
            'allowVAT' => 'bool',
            'displayStateCode' => 'bool',
            'isoCountryNumber' => '',
            'dateFormat' => '',
            'timeFormat' => '',
            'numericFormat' => '',
            'defaultISOCountryCode' => '',
        ];
    }
}