<?php

namespace Blackbox\Epace\Model\Epace\Estimate;

class Product extends \Blackbox\Epace\Model\Epace\Estimate\AbstractChild
{
    protected function _construct()
    {
        $this->_init('EstimateProduct', 'id');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Estimate_Part[]
     */
    public function getParts()
    {
        return $this->_getChildItems('efi/estimate_part_collection', [
            'estimateProduct' => (int)$this->getId()
        ], function ($part) {
            $part->setEstimate($this->getEstimate())->setProduct($this);
        });
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Estimate_Product_PriceSummary[]
     */
    public function getPriceSummaries()
    {
        return $this->_getChildItems('efi/estimate_product_priceSummary_collection', [
            'estimateProduct' => (int)$this->getId()
        ], function ($priceSummary) {
            $priceSummary->setProduct($this);
        });
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'sequence' => 'float',
            'estimate' => 'int',
            'description' => 'string',
            'systemGenerated' => 'bool',
            'singleWebDelivery' => 'bool',
            'manufacturingLocation' => 'int',
            'lookupUrl' => 'string',
            'wrapRearWindow' => 'bool',
            'wrapSideWindow' => 'bool',
            'secondSurface' => 'bool'
        ];
    }
}