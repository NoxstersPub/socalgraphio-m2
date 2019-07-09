<?php

namespace Blackbox\Epace\Model\Epace\Job\Part;

class Item extends \Blackbox\Epace\Model\Epace\Job\Part\AbstractChild
{
    protected function _construct()
    {
        $this->_init('JobPartItem', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'price' => 'float',
            'manual' => 'bool',
            'quoteItemType' => 'int',
            'quantityOverride' => 'bool',
            'quantityValue' => 'float',
            'originalQuantity' => 'float',
            'quantityMultiplier' => 'float',
            'qtyOrdered' => 'float',
            'inventoryQuantityForced' => 'bool',
            'inventoryQtyOverride' => 'bool',
            'unitPriceOverride' => 'bool',
            'unitPrice' => 'float',
            'flatPriceOverride' => 'bool',
            'flatPrice' => 'float',
            'adjustValue' => 'float',
            'finalPrice' => 'float',
            'lockFinalPrice' => 'bool',
            'job' => 'string',
            'jobPart' => 'string',
            'name' => 'string',
            'sequence' => 'float',
            'inventoryUnitPrice' => 'float',
            'inventoryUnitPriceForced' => 'bool',
            'customerViewable' => 'bool',
            'quantity' => 'float',
            'adjustedPrice' => 'float',
            'quantityRemaining' => 'float',
            'JobPartKey' => 'string',
        ];
    }
}