<?php
namespace Blackbox\Epace\Model\Epace\Job\Part;

class SizeAllowance extends \Blackbox\Epace\Model\Epace\Job\Part\AbstractChild
{
    protected function _construct()
    {
        $this->_init('JobPartSizeAllowance', 'id');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\SizeAllowanceType
     */
    public function getSizeAllowanceType()
    {
        return $this->_getObject('sizeAllowanceType', 'sizeAllowanceType', 'efi/sizeAllowanceType', true);
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\SizeAllowanceType $type
     * @return $this
     */
    public function setSizeAllowanceType(\Blackbox\Epace\Model\Epace\SizeAllowanceType $type)
    {
        return $this->_setObject('sizeAllowanceType', $type);
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'job' => '',
            'jobPart' => '',
            'sizeAllowanceType' => '',
            'head' => '',
            'headAllowanceExpression' => '',
            'spine' => '',
            'spineAllowanceExpression' => '',
            'face' => '',
            'faceAllowanceExpression' => '',
            'foot' => '',
            'footAllowanceExpression' => '',
            'numOddPanelsSpine' => '',
            'numOddPanelsWidth' => '',
            'oddPanelSpineSize' => '',
            'oddPanelWidthSize' => '',
            'spineWidth' => '',
            'calculatedHead' => '',
            'calculatedFoot' => '',
            'calculatedSpine' => '',
            'calculatedFace' => '',
            'JobPartKey' => '',
        ];
    }
}