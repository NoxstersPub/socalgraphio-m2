<?php

namespace Blackbox\Epace\Model\Epace\Estimate;

use \Blackbox\Epace\Model\Epace\Estimate\Part\ChildTrait;

class Quantity extends \Blackbox\Epace\Model\Epace\Estimate\AbstractChild
{
    

    protected function _construct()
    {
        $this->_init('EstimateQuantity', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'estimatePart' => 'int',
            'quantityOrdered' => '',
            'metrixID' => '',
            'price' => '',
            'taxBase' => '',
            'taxAmount' => '',
            'pricePerAddlM' => '',
            'cost' => '',
            'targetSell' => '',
            'markup' => '',
            'markupPercent' => '',
            'grandTotal' => '',
            'overallMarkupForced' => '',
            'valueAddedPrice' => '',
            'nonValueAddedPrice' => '',
            'valueAddedMarkupForced' => '',
            'nonValueAddedMarkupForced' => '',
            'overallSellMarkup' => '',
            'overallSellMarkupForced' => '',
            'gripperColorBar' => '',
            'sheetsOffPress' => '',
            'numSigsPerPressForm' => '',
            'numSigsOddPressForm' => '',
            'position' => '',
            'quotedPrice' => '',
            'quotedPriceForced' => '',
            'quotedPricePerAddlM' => '',
            'quotedPricePerAddlMForced' => '',
            'valueAdded' => '',
            'nonValueAdded' => '',
            'valueAddedPercent' => '',
            'nonValueAddedPercent' => '',
            'weightPerPiece' => '',
            'comboPercent' => '',
            'comboPercentForced' => '',
            'pricePerEach' => '',
            'pricePerEachForced' => '',
            'quotedPricePerAddl100' => '',
            'quotedPricePerAddl100Forced' => '',
            'pricePerAddl100' => '',
            'pricePerAddl100Forced' => '',
            'newQuantity' => '',
            'dirty' => '',
            'paperMarkup' => '',
            'paperMarkupForced' => '',
            'outsidePurchaseMarkupForced' => '',
            'outsidePurchaseSetupMarkupForced' => '',
            'alternatePrintMethodApplied' => '',
            'contributionAnalysisTaxAmount' => '',
            'pricePerUOM' => '',
            'pricePerUOMForced' => '',
            'pricePerAddlUOM' => '',
            'pricePerAddlUOMForced' => '',
            'pricingUnits' => '',
            'pricingUnitsForced' => '',
            'effectiveCommissionRateForced' => '',
            'estimate' => 'int',
            'chartDescription' => '',
            'valueAddedPerPressHour' => '',
            'overallInkCoverageSide1' => '',
            'overallInkCoverageSide2' => '',
            'commRate' => '',
            'additionalWeightPerPiece' => '',
        ];
    }
}