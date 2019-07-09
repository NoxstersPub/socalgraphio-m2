<?php
namespace Blackbox\Epace\Model\Epace\Estimate\Part;

use \Blackbox\Epace\Model\Epace\Estimate\Part\ChildTrait;

class SizeAllowance extends \Blackbox\Epace\Model\Epace\AbstractObject
{
    protected function _construct()
    {
        $this->_init('EstimatePartSizeAllowance', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'estimatePart' => 'int',
            'state' => '',
            'source' => '',
            'sizeAllowanceType' => '',
            'head' => '',
            'spine' => '',
            'face' => '',
            'foot' => '',
            'numOddPanelsSpine' => '',
            'numOddPanelsWidth' => '',
            'oddPanelSpineSize' => '',
            'oddPanelWidthSize' => '',
            'spineWidth' => '',
            'spineWidthForced' => '',
            'headAllowanceExpression' => '',
            'footAllowanceExpression' => '',
            'spineAllowanceExpression' => '',
            'faceAllowanceExpression' => '',
            'calculatedHead' => '',
            'calculatedFoot' => '',
            'calculatedSpine' => '',
            'calculatedFace' => '',
        ];
    }
}