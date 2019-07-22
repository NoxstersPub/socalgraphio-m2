<?php

namespace Blackbox\Epace\Model\Epace;

use \Blackbox\Epace\Model\Epace\PersonsTrait;

class Job extends \Blackbox\Epace\Model\Epace\EpaceObject {

    protected function _construct() {
        $this->_init('Job', 'job');
    }

    /**
     * @return int
     */
    public function getTypeId() {
        return $this->getData('jobType');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Type|false
     */
    public function getType() {
        return $this->_getObject('type', 'jobType', 'efi/job_type');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Job_Type $type
     * @return $this
     */
    public function setType(\Blackbox\Epace\Model\Epace\Job\Type $type) {
        return $this->_setObject('type', $type);
    }

    public function getAdminStatusCode() {
        return $this->getData('adminStatus');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Status|bool
     */
    public function getAdminStatus() {
        return $this->_getObject('status', 'adminStatus', 'efi/job_status', true);
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Job_Status $status
     * @return $this
     */
    public function setAdminStatus(\Blackbox\Epace\Model\Epace\Job\Status $status) {
        return $this->_setObject('status', $status);
    }

    /**
     * @return string
     */
    public function getPrevAdminStatusCode() {
        return $this->getData('prevAdminStatus');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Status|bool
     */
    public function getPrevAdminStatus() {
        return $this->_getObject('prevStatus', 'prevAdminStatus', 'efi/job_status', true);
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Job_Status $status
     * @return $this
     */
    public function setPrevAdminStatus(\Blackbox\Epace\Model\Epace\Job\Status $status) {
        return $this->_setObject('prevStatus', $status);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Quote
     */
    public function getQuote() {
        return $this->_getObject('quote', 'quoteNumber', 'efi/quote');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Quote $quote
     * @return $this
     */
    public function setQuote(\Blackbox\Epace\Model\Epace\Quote $quote) {
        return $this->_setObject('quote', $quote);
    }

    /**
     * @return bool
     */
    public function isSourceEstimate() {
        return $this->getData('altCurrencyRateSource') == 'Estimate';
    }

    /**
     * @return int|string
     */
    public function getEstimateId() {
        if ($this->isSourceEstimate()) {
            return $this->getAltCurrencyRateSourceNote();
        } else {
            return '';
        }
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Estimate
     */
    public function getEstimate() {
        if (!$this->_hasObjectField('estimate')) {
            $this->_setObject('estimate', false);

            if ($this->isSourceEstimate()) {
                $estimate = $this->_loadObject('efi/estimate', $this->getData('altCurrencyRateSourceNote'));
                if ($estimate->getId()) {
                    $this->_setObject('estimate', $estimate);
                }
            }
        }

        return $this->_getObjectField('estimate');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Estimate $estimate
     * @return $this
     */
    public function setEstimate(\Blackbox\Epace\Model\Epace\Estimate $estimate) {
        return $this->_setObject('estimate', $estimate);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Ship_Via
     */
    public function getShipVia() {
        return $this->_getObject('shipVia', 'shipVia', 'efi/ship_via', true);
    }

    /**
     * @return int
     */
    public function getBillToJobContactId() {
        return $this->getData('billToJobContact');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Contact
     */
    public function getBillToJobContact() {
        return $this->_getObject('billToJobContact', 'billToJobContact', 'efi/job_contact', false, function (\Blackbox\Epace\Model\Epace\Job\Contact $jobContact) {
                    $jobContact->setJob($this);
                });
    }

    /**
     * @return int
     */
    public function getShipToJobContactId() {
        return $this->getData('shipToJobContact');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Contact
     */
    public function getShipToJobContact() {
        return $this->_getObject('shipToJobContact', 'shipToJobContact', 'efi/job_contact', false, function (\Blackbox\Epace\Model\Epace\Job\Contact $jobContact) {
                    $jobContact->setJob($this);
                });
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Product[]
     */
    public function getProducts() {
        return $this->_getJobItems('efi/job_product_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Contact[]
     */
    public function getJobContacts() {
        return $this->_getJobItems('efi/job_contact_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Invoice[]
     */
    public function getInvoices() {
        return $this->_getJobItems('efi/invoice_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Shipment[]
     */
    public function getShipments() {
        return $this->_getJobItems('efi/job_shipment_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Note[]
     */
    public function getNotes() {
        return $this->_getJobItems('efi/job_note_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Part[]
     */
    public function getParts() {
        /** @var \Blackbox\Epace\Model\Epace\Job_Part[] $parts */
        $parts = $this->_getJobItems('efi/job_part_collection');
        if ($estimate = $this->getEstimate()) {
            foreach ($parts as $part) {
                if ($part->getData('estimate') == $estimate->getData('estimateNumber')) {
                    $part->setEstimate($estimate);
                }
            }
        }

        return $parts;
    }

    public function getDefinition() {
        return [
            'addPlanFromJobType' => '',
            'epaceEstimate' => '',
            'billPartsTogether' => '',
            'customer' => 'string',
            'description' => '',
            'description2' => '',
            'enteredBy' => '',
            'job' => 'string',
            'jobType' => 'int',
            'totalParts' => '',
            'salesPerson' => 'int',
            'csr' => 'int',
            'adminStatus' => 'string',
            'prevAdminStatus' => 'string',
            'shipVia' => 'int',
            'terms' => '',
            'allowableOvers' => '',
            'dateSetup' => 'date',
            'timeSetUp' => 'date',
            'poNum' => 'string',
            'promiseDate' => 'date',
            'scheduledShipDateForced' => '',
            'scheduledShipTimeForced' => '',
            'contactFirstName' => '',
            'contactLastName' => '',
            'priority' => '',
            'quoteNumber' => '',
            'priceList' => '',
            'oversMethod' => '',
            'shipInNameOf' => '',
            'numbersGuaranteed' => '',
            'convertingToJob' => '',
            'jobOrderType' => '',
            'comboJobPercentageCalculationType' => '',
            'freightOnBoard' => '',
            'altCurrency' => '',
            'altCurrencyRate' => '',
            'altCurrencyRateSource' => '',
            'altCurrencyRateSourceNote' => 'string',
            'createdFromAnsix12850' => '',
            'readyToSchedule' => '',
            'useLegacyPrintFlowFormatPrePress' => '',
            'useLegacyPrintFlowFormatFinishing' => '',
            'billToJobContact' => '',
            'shipToJobContact' => '',
            'jdfSubmitted' => '',
            'jobValue' => '',
            'destinationBasedTaxing' => '',
            'dsfCreditCardFinalized' => '',
            'invoiceW2POrderAmount' => '',
            'invoiceW2PShippingAmount' => '',
            'invoiceW2PTaxAmount' => '',
            'invoiceW2PHandlingAmount' => '',
            'manufacturingLocation' => '',
            'taxCategoryForced' => '',
            'comboDirty' => '',
            'invoiceLevelOptions' => '',
            'amountToInvoice' => '',
            'amountToInvoiceForced' => '',
            'quantityOrdered' => '',
            'quantityOrderedForced' => '',
            'originalQuotedPrice' => '',
            'originalQuotedPriceForced' => '',
            'salesCategory' => '',
            'salesCategoryForced' => '',
            'invoiceUOM' => '',
            'invoiceUOMForced' => '',
            'amountInvoiced' => '',
            'changeOrderTotal' => '',
            'freightAmountTotal' => '',
            'executeSync' => '',
            'comboTotal' => '',
            'currentStatus' => '',
            'earliestProofDue' => '',
            'earliestProofShipDateTime' => '',
            'totalPriceAllParts' => '',
            'quantityRemaining' => '',
            'qtyOrdered' => '',
            'scheduled' => '',
            'prePressScheduled' => '',
            'finishingScheduled' => '',
            'promptForMultipleParts' => '',
            'promptForMultipleProducts' => '',
            'billPartsTogetherAttribute' => '',
            'billPartOneOnlyAttribute' => '',
        ];
    }

    protected function _getJobItems($collectionName) {
        return $this->_getChildItems($collectionName, [
                    'job' => $this->getId()
                        ], function ($item) {
                    $item->setJob($this);
                });
    }

}
