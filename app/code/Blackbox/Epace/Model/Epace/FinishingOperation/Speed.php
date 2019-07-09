<?php

class Blackbox_Epace_Model_Epace_FinishingOperation_Speed extends Blackbox_Epace_Model_Epace_AbstractObject
{
    protected function _construct()
    {
        $this->_init('FinishingOperationSpeed', 'id');
    }

    /**
     * @return Blackbox_Epace_Model_Epace_FinishingOperation|false
     */
    public function getFinisingOperation()
    {
        return $this->_getObject('finishingOperation', 'finishingOperation', 'efi/finishingOperation');
    }

    /**
     * @param Blackbox_Epace_Model_Epace_FinishingOperation $operation
     * @return $this
     */
    public function setFinishingOperation(Blackbox_Epace_Model_Epace_FinishingOperation $operation)
    {
        return $this->_setObject('finishingOperation', $operation);
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'finishingOperation' => 'int',
            'quantity' => 'float',
            'unitsPerHour' => 'float',
            'spoilage' => 'float',
            'costPerM' => 'float',
        ];
    }
}