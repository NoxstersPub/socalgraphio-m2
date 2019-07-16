<?php
namespace Blackbox\Epace\Model\Epace\Estimate\QuoteLetter;
class Note extends \Blackbox\Epace\Model\Epace\EpaceObject
{
    protected function _construct()
    {
        $this->_init('EstimateQuoteLetterNote', 'id');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Estimate_QuoteLetter|bool
     */
    public function getEstimateQuoteLetter()
    {
        return $this->_getObject('estimateQuoteLetter', 'estimateQuoteLetter', 'efi/estimate_quoteLetter');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Estimate_QuoteLetter $quoteLetter
     * @return $this
     */
    public function setEstimateQuoteLetter(\Blackbox\Epace\Model\Epace\Estimate\QuoteLetter $quoteLetter)
    {
        return $this->_setObject('estimateQuoteLetter', $quoteLetter);
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'estimateQuoteLetter' => 'int',
            'note' => 'string',
            'printOnReport' => 'bool',
            'section' => 'string',
            'category' => 'string',
            'useStandardSpaceFont' => 'bool',
            'sequence' => 'float',
            'product' => 'float',
            'part' => 'float',
        ];
    }
}