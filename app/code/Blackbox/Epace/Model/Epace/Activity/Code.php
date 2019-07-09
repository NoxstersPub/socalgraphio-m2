<?php
namespace Blackbox\Epace\Model\Epace\Activity;

class Code extends \Blackbox\Epace\Model\Epace\AbstractObject
{
    protected function _construct()
    {
        $this->_init('ActivityCode', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'string',
            'description' => 'string',
            'chargeBasis' => 'int',
            'revenueProducing' => 'bool',
            'standProdUnitsPerH' => 'float',
            'hrsPerProdUnit' => 'float',
            'matlQtyPerUnit' => 'string',
            'salesCategory' => 'int',
            'maxQuantity' => 'string',
            'updateCurDept' => 'bool',
            'matlPrompt' => 'string',
            'askInventory' => 'string',
            'askProdUnit' => 'string',
            'askNonPlannedReason' => 'string',
            'askNotes' => 'string',
            'addJobTracking' => 'bool',
            'wipCategory' => 'int',
            'costCenter' => 'string',
            'active' => 'bool',
            'askCounts' => 'string',
            'askPostage' => 'string',
            'planningIntegration' => 'string',
            'updatePlanning' => 'bool',
            'excludeProdUnitsInRollup' => 'bool',
            'leadTime' => 'float',
            'lagTime' => 'float',
            'askIfComplete' => 'bool',
            'outsidePurchase' => 'bool',
            'askQuantityOfMaterials' => 'string',
            'inventoryPrompt' => 'int',
            'includeNonInventoryItems' => 'bool',
            'createActualCosts' => 'bool',
            'estimateResultType' => 'string',
            'wipDebit' => 'int',
            'wipCredit' => 'int',
            'cogsDebit' => 'int',
            'cogsCredit' => 'int',
            'paper' => 'bool',
            'useComboSplit' => 'bool',
            'jobPlanLevel' => 'int',
            'planByPass' => 'bool',
            'updateJobPartLocation' => 'bool',
            'planningTimeCalculation' => 'int',
            'plantManagerDMICategory' => 'int',
            'plantManagerReportCategory' => 'int',
            'laborCostCategory' => 'int',
            'laborOverheadCategory' => 'int',
            'machineCostCategory' => 'int',
            'generalOACategory' => 'int',
            'markupCategory' => 'int',
            'materialOtherCategory' => 'int',
            'costMarkupCategory' => 'int',
            'includeInValueAdded' => 'bool',
            'includeAsCost' => 'bool',
            'consolidateExtras' => 'bool',
            'multipleUpstreamTasks' => 'bool',
        ];
    }
}