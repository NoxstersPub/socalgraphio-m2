<?php

namespace Blackbox\Epace\Model\Epace\Job;

class Product extends \Blackbox\Epace\Model\Epace\Job\EpaceChild
{
    protected function _construct()
    {
        $this->_init('JobProduct', 'id');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Part[]
     */
    public function getParts()
    {
        return $this->_getChildItems('efi/job_part_collection', [
            'jobProduct' => (int)$this->getId()
        ], function ($part) {
            if ($this->getJob()) {
                $part->setJob($this->getJob());
            }
            $part->setProduct($this);
        });
    }

    public function getDefinition()
    {
        return [
            'sequence' => '',
            'id' => 'int',
            'proof' => '',
            'job' => '',
            'description' => '',
            'qtyOrdered' => '',
            'qtyOrderedForced' => '',
            'jdfSubmitted' => '',
            'singleWebDelivery' => '',
            'productValue' => '',
            'manufacturingLocation' => '',
            'taxCategoryForced' => '',
            'lookupURL' => '',
            'wrapRearWindow' => '',
            'wrapSideWindow' => '',
            'secondSurface' => '',
            'estimateSource' => '',
            'amountToInvoice' => '',
            'amountToInvoiceForced' => '',
            'salesCategory' => '',
            'salesCategoryForced' => '',
            'originalQuotedPrice' => '',
            'originalQuotedPriceForced' => '',
            'invoiceUOM' => '',
            'invoiceUOMForced' => '',
            'qtyRemaining' => '',
        ];
    }
}