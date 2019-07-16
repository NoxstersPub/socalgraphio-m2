<?php

namespace Blackbox\Epace\Model\Epace\Estimate;

class QuoteLetter extends \Blackbox\Epace\Model\Epace\Estimate\AbstractChild
{
    protected function _construct()
    {
        $this->_init('EstimateQuoteLetter', 'id');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Estimate_QuoteLetter_Note[]
     */
    public function getNotes()
    {
        return $this->_getChildItems('efi/estimate_quoteLetter_note_collection', [
            'estimateQuoteLetter' => $this->getId()
        ], function ($item) {
            $item->setQuoteLetter($this);
        });
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'printPriceGrid' => 'bool',
            'quoteLetterType' => 'int',
            'estimate' => 'int',
            'date' => 'date',
            'salutation' => 'string',
            'body' => 'string',
            'comment' => 'string',
            'closing' => 'string',
            'internalNote' => 'string',
            'accepted' => 'bool',
            'priceDetailLevel' => '',
        ];
    }
}