<?php
namespace Blackbox\Epace\Model\Epace;

class FinishingOperation extends \Magento\Framework\Model\Epace\AbstractModel
{
    protected function _construct()
    {
        $this->_init('FinishingOperation', 'id');
    }

    /**
     * @return Blackbox_Epace_Model_Epace_FinishingOperation_Speed[]
     */
    public function getSpeeds()
    {
        return $this->_getChildItems('efi/finishingOperation_speed_collection', [
            'finishingOperation' => $this->getId()
        ], function ($item) {
            $item->setFinishingOperation($this);
        });
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'description' => '',
            'quantityCalcMethod' => '',
            'materialsActivityCode' => '',
            'makeReadyActivityCode' => '',
            'runActivityCode' => '',
            'helperActivityCode' => '',
            'quantityMultiplier' => '',
            'finishingGroup' => '',
            'unitLabel' => '',
            'category' => '',
            'rotateStack' => 'bool',
            'active' => 'bool',
            'useMachineActivityCodes' => 'bool',
            'customerViewable' => 'bool',
            'nonComboQuantity' => 'bool',
            'useUptoSize' => 'bool',
            'removeIfNotRequired' => 'bool',
            'materialsIncludedInAdditionalPieceWeight' => 'bool',
        ];
    }
}