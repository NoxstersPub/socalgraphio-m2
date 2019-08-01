<?php

namespace Blackbox\Epace\Model\Epace;

use \Blackbox\Epace\Model\Epace\PersonsTrait;

class Estimate extends \Blackbox\Epace\Model\Epace\EpaceObject
{   

    public function _construct()
    {
        $this->_init('Estimate', 'id');
    }

    public function getStatusId()
    {
        return $this->getData('status');
    }

    /**
     * @return \Blackbox\Epace\Model\Estimate_Status
     */
    public function getStatus()
    {
        return $this->_getObject('status', 'status', 'efi/estimate_status', true);
    }

    /**
     * @param \Blackbox\Epace\Model\Estimate_Status $status
     * @return $this
     */
    public function setStatus(\Blackbox\Epace\Model\Epace\Estimate\Status $status)
    {
        return $this->_setObject('status', $status);
    }

    /**
     * @return \Blackbox\Epace\Model\Estimate_Product[]
     */
    public function getProducts()
    {
        return $this->_getEstimateItems('efi/estimate_product_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Estimate_QuoteLetter[]
     */
    public function getQuoteLetters()
    {
        return $this->_getEstimateItems('efi/estimate_quoteLetter_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Estimate_Quantity[]
     */
    public function getQuantities()
    {
        return $this->_getEstimateItems('efi/estimate_quantity_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Estimate_Part[]
     */
    public function getParts()
    {
        return $this->_getEstimateItems('efi/estimate_part_collection');
    }

    /**
     * @return bool
     */
    public function isConvertedToJob()
    {
        return $this->getData('status') == \Blackbox\Epace\Model\Epace\Estimate\Status::STATUS_CONVERTED_TO_JOB;
    }

    /**
     * @return \Blackbox\Epace\Model\Job[]
     */
    public function getJobs()
    {
        return $this->_getChildItems('efi/job_collection', [
            'altCurrencyRateSource' => 'Estimate',
            'altCurrencyRateSourceNote' => $this->getId()
        ], function ($item) {
            if ($this->getId() == $item->getEstimateId()) {
                $item->setEstimate($this);
            }
        });
    }

    /**
     * @return \Blackbox\Epace\Model\Job|bool
     */
    public function getLastJob()
    {
        if ($this->isConvertedToJob() || $this->hasObject('job')) {
            return $this->_getObject('job', 'lastJob', 'efi/job');
        } else {
            return false;
        }
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'estimateNumber' => '',
            'priceSummaryLevel' => '',
            'fromCombo' => '',
            'salesPerson' => 'int',
            'csr' => 'int',
            'estimator' => '',
            'entryDate' => 'date',
            'entryTime' => 'date',
            'enteredBy' => '',
            'followUpDate' => 'date',
            'customer' => 'string',
            'customerProspectName' => '',
            'prospectName' => '',
            'description' => '',
            'notes' => '',
            'status' => 'int',
            'rewardDate' => 'date',
            'lastJob' => '',
            'estimateRequest' => '',
            'shipToContact' => '',
            'billToContact' => '',
            'taxableCode' => '',
            'addCRMOpportunity' => '',
            'addCRMActivity' => '',
            'freightOnBoard' => '',
            'debug' => '',
            'altCurrency' => '',
            'altCurrencyRate' => '',
            'altCurrencyRateSource' => '',
            'altCurrencyRateSourceNote' => 'string',
            'forceQuotedPriceOnConvert' => '',
            'committedFromMetrix' => '',
            'allowVAT' => '',
            'repetitiveRuns' => '',
            'manufacturingLocation' => '',
            'estimateVersionNumber' => 'int',
            'nextEstimateVersionNumber' => 'int',
            'highestEstimateVersion' => '',
            'autoAddQuoteLetter' => '',
            'lastChangedDate' => 'date',
            'lastChangedTime' => 'date',
            'lastChangedBy' => '',
            'totalParts' => '',
            'totalPages' => ''
        ];
    }

    protected function _getEstimateItems($collectionName)
    {
        return $this->_getChildItems($collectionName, [
            'estimate' => (int)$this->getId()
        ], function ($item) {
            $item->setEstimate($this);
        });
    }
}