<?php

namespace Blackbox\Epace\Model\Epace\Job;

class Part extends \Blackbox\Epace\Model\Epace\Job\EpaceChild
    implements \Blackbox\Epace\Model\Resource\Epace\CombinedKeyInterface
{
    protected function _construct()
    {
        $this->_init('JobPart', 'primaryKey');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Product
     */
    public function getProduct()
    {
        return $this->_getObject('product', 'jobProduct', 'efi/job_product');
    }

    public function setProduct(\Blackbox\Epace\Model\Epace\Job\Product $product)
    {
        return $this->_setObject('product', $product);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Estimate|bool
     */
    public function getEstimate()
    {
        if (!$this->_hasObjectField('estimate')) {
            $this->_setObject('estimate', false);
            if ($this->getData('estimate')) {
                /** @var Blackbox_Epace_Model_Resource_Epace_Estimate_Collection $collection */
                $collection = $this->_getCollection('efi/estimate_collection');
                $collection->addFilter('estimateNumber', $this->getData('estimate'));
                $collection->setPageSize(1)->setCurPage(1);
                $estimate = $collection->getFirstItem();
                if ($estimate && $estimate->getId()) {
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
    public function setEstimate(\Blackbox\Epace\Model\Epace\Estimate $estimate)
    {
        return $this->_setObject('estimate', $estimate);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Estimate_Part|false
     */
    public function getEstimatePart()
    {
        if (!$this->_hasObjectField('estimatePart')) {
            $this->_setObject('estimatePart', false);

            if (!empty($this->getData('estimatePart')) && $this->getJob()->getEstimate()) {
                $targetNum = (int)$this->getData('estimatePart');
                $i = 0;
                $part = null;
                foreach ($this->getJob()->getEstimate()->getParts() as $_part) {
                    if (++$i == $targetNum) {
                        $part = $_part;
                        break;
                    }
                }
                if ($part) {
                    $this->_setObject('estimatePart', $part);
                }
            }
        }

        return $this->_getObjectField('estimatePart');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Estimate_Part $estimatePart
     * @return $this
     */
    public function setEstimatePart(\Blackbox\Epace\Model\Epace\Estimate\Part $estimatePart)
    {
        return $this->_setObject('estimatePart', $estimatePart);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Estimate_Quantity
     */
    public function getEstimateQuantity()
    {
        if (!$this->_hasObjectField('estimateQuantity')) {
            $this->_setObject('estimateQuantity');

            if ($estimatePart = $this->getEstimatePart()) {
                $qtyOrdered = $this->getQtyOrdered();
                foreach ($estimatePart->getQuantities() as $quantity) {
                    if ($quantity->getQuantityOrdered() == $qtyOrdered) {
                        $this->_setObject('estimateQuantity', $quantity);
                        break;
                    }
                }
            }
        }

        return $this->_getObjectField('estimateQuantity');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Estimate_Quantity $estimateQuantity
     * @return $this
     */
    public function setEstimateQuantity(\Blackbox\Epace\Model\Epace\Estimate\Quantity $estimateQuantity)
    {
        return $this->_setObject('estimateQuantity', $estimateQuantity);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Material[]
     */
    public function getMaterials()
    {
        return $this->_getPartItems('efi/job_material_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Part_PrePressOp[]
     */
    public function getPrePressOps()
    {
        return $this->_getPartItems('efi/job_part_prePressOp_collection');
    }

    /**
     * @return Blackbox_Epace_model_Epace_Change_Order[]
     */
    public function getChangeOrders()
    {
        return $this->_getPartItems('efi/change_order_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Proof[]
     */
    public function getProofs()
    {
        return $this->_getPartItems('efi/proof_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Part_Item[]
     */
    public function getItems()
    {
        return $this->_getPartItems('efi/job_part_item_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Part_PressForm[]
     */
    public function getPressForms()
    {
        return $this->_getPartItems('efi/job_part_pressForm_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Component[]
     */
    public function getComponents()
    {
        return $this->_getPartItems('efi/job_component_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Part_FinishingOp[]
     */
    public function getFinishingOps()
    {
        return $this->_getPartItems('efi/job_part_finishingOp_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Part_OutsidePurch[]
     */
    public function getOutsidePurchs()
    {
        return $this->_getPartItems('efi/job_part_outsidePurch_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Plan[]
     */
    public function getPlans()
    {
        return $this->_getPartItems('efi/job_plan_collection', [
            'job' => $this->getData('job'),
            'part' => $this->getData('jobPart')
        ]);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Cost[]
     */
    public function getCosts()
    {
        return $this->_getPartItems('efi/job_cost_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Part_SizeAllowance[]
     */
    public function getSizeAllowances()
    {
        return $this->_getPartItems('efi/job_part_sizeAllowance_collection');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Invoice[]
     */
    public function getInvoices()
    {
        return $this->_getPartItems('efi/invoice_collection');
    }

    public function getDefinition()
    {
        return [
            'visualOpeningSizeHeight' => '',
            'visualOpeningSizeWidth' => '',
            'allowancesChanged' => 'bool',
            'metrixEnabled' => 'bool',
            'gripperColorBar' => '',
            'metrixID' => '',
            'metrixComponentID' => '',
            'billRate' => '',
            'billUOM' => '',
            'binderyMethod' => '',
            'binderyWork' => '',
            'bleedsAcross' => '',
            'bleedsAlong' => '',
            'colorsS1' => '',
            'colorsS2' => '',
            'colorsTotal' => '',
            'dateSetup' => 'date',
            'description' => '',
            'desktop' => '',
            'estCostPerM' => '',
            'estimate' => 'string',// estimate number
            'estimatePart' => 'string',// index number, e.g. 01
            'estimatedCost' => '',
            'estimator' => '',
            'flatSizeH' => '',
            'flatSizeW' => '',
            'finalSizeH' => '',
            'finalSizeW' => '',
            'trimSizeHeight' => '',
            'trimSizeWidth' => '',
            'folderNumUp' => '',
            'foldPattern' => '',
            'foldPatternDesc' => '',
            'grainSpecifications' => '',
            'inkDescS1' => '',
            'invoiceSequence' => '',
            'job' => 'string',
            'jobCost01' => '',
            'jobPart' => 'string',
            'jobType' => '',
            'lastActCode' => '',
            'lastActDate' => 'date',
            'lastActTime' => 'date',
            'lastCODate' => 'date',
            'lastCODept' => 'int',
            'lastCOTime' => 'date',
            'materialProvided' => '',
            'numPrsShtsOut' => '',
            'numSigs' => '',
            'loadBalanced' => 'bool',
            'pages' => '',
            'parallelFolds' => '',
            'plates' => '',
            'prep' => '',
            'separateLayout' => 'bool',
            'press1' => '',
            'pressSheetNumOut' => '',
            'jobProductType' => '',
            'priority' => '',
            'productionStatus' => '',
            'proofs' => '',
            'qtyOrdered' => '',
            'qtyToMfg' => '',
            'quotedPrice' => '',
            'quotedPriceForced' => 'bool',
            'quotePerM' => '',
            'rightFolds' => '',
            'runMethod' => '',
            'salesCategory' => '',
            'sheetsNetRequired' => '',
            'sheetsOffPress' => '',
            'sheetsToPress' => '',
            'stitcherNumUp' => '',
            'timeSetUp' => '',
            'totalHours' => '',
            'useBasicJacket' => 'bool',
            'numPressForms' => '',
            'numSigsOddPressForm' => '',
            'numPlies' => '',
            'calculating' => 'bool',
            'manufacturingLocation' => '',
            'originalManufacturingLocation' => '',
            'jogTrim' => '',
            'nonImageHead' => '',
            'nonImageFoot' => '',
            'nonImageSpine' => '',
            'nonImageFace' => '',
            'run' => '',
            'paceConnectFileType' => '',
            'shippingWorkflow' => '',
            'prepressWorkflow' => '',
            'useLegacyPrintFlowFormat' => 'bool',
            'directMailPart' => 'bool',
            'bindingSide' => '',
            'jogSide' => '',
            'jdfSubmitted' => 'bool',
            'lastStatusChangedDate' => 'date',
            'lastStatusChangedTime' => 'date',
            'proofRequired' => 'bool',
            'proofPart' => 'bool',
            'resolution' => '',
            'tileProduct' => 'bool',
            'seamDirection' => '',
            'usePressForms' => 'bool',
            'gangable' => 'bool',
            'jobProduct' => '',
            'finishedAutoImport' => 'bool',
            'value' => '',
            'productionType' => '',
            'queueDestination' => '',
            'requiresImposition' => 'bool',
            'componentDescription' => '',
            'estimateVersion' => '',
            'invoiceW2POrderAmount' => 'bool',
            'invoiceW2PShippingAmount' => 'bool',
            'invoiceW2PTaxAmount' => 'bool',
            'invoiceW2PHandlingAmount' => 'bool',
            'printRunMethod' => '',
            'mxmlLayoutInvalid' => 'bool',
            'originalQuotedPrice' => '',
            'originalQuotedPriceForced' => 'bool',
            'originalQuotedPricePerM' => '',
            'originalQuotedPricePerMForced' => 'bool',
            'transactionHours' => '',
            'transactionCosts' => '',
            'colors' => '',
            'totalCost' => '',
            'targetSellPrice' => '',
            'quantityRemaining' => '',
            'scheduled' => 'bool',
            'includeMailing' => 'bool',
            'calculatedTabSpine' => '',
            'calculatedTabFace' => '',
            'calculatedTabHead' => '',
            'calculatedTabFoot' => '',
            'calculatedBleedsSpine' => '',
            'calculatedBleedsFace' => '',
            'calculatedBleedsHead' => '',
            'calculatedBleedsFoot' => '',
            'calculatedTrimSpine' => '',
            'calculatedTrimFace' => '',
            'calculatedTrimHead' => '',
            'calculatedTrimFoot' => '',
            'calculatedOddPanelSpineSize' => '',
            'calculatedNumOddPanelsSpine' => '',
            'calculatedOddPanelWidthSize' => '',
            'calculatedNumOddPanelsWidth' => '',
            'calculatedSpineSize' => '',
            'primaryKey' => '',
            'foldPatternKey' => '',
        ];
    }

    public function getPrimaryKeyFields()
    {
        return [
            'job',
            'jobPart'
        ];
    }

    /**
     * @param $collectionName
     * @return \Blackbox\Epace\Model\Epace\Job_Part_AbstractChild[]
     */
    protected function _getPartItems($collectionName, $filters = null)
    {
        if (!$filters) {
            $filters = [
                'job' => $this->getData('job'),
                'jobPart' => $this->getData('jobPart')
            ];
        }
        $job = $this->getJob();
        return $this->_getChildItems($collectionName, $filters, function ($item) use ($job) {
            if ($job) {
                $item->setJob($job);
            }
            $item->setPart($this);
        });
    }
}