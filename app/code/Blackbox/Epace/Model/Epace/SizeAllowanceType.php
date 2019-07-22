<?php

namespace Blackbox\Epace\Model\Epace;

class SizeAllowanceType extends \Blackbox\Epace\Model\Epace\EpaceObject
{
    protected function _construct()
    {
        $this->_init('SizeAllowanceType', 'id');
    }

    public function getHeadAllowanceExpression()
    {
        // TODO: Implement getHeadAllowanceExpression() method.
    }

    public function getFootAllowanceExpression()
    {
        // TODO: Implement getFootAllowanceExpression() method.
    }

    public function getSpineAllowanceExpression()
    {
        // TODO: Implement getSpineAllowanceExpression() method.
    }

    public function getFaceAllowanceExpression()
    {
        // TODO: Implement getFaceAllowanceExpression() method.
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'description' => '',
            'systemGenerated' => 'bool',
            'sizeAllowanceType' => 'string',
            'specifyHead' => 'bool',
            'headAllowanceExpression' => 'int',
            'specifyFoot' => 'bool',
            'footAllowanceExpression' => 'int',
            'specifySpine' => 'bool',
            'spineAllowanceExpression' => 'int',
            'specifyFace' => 'bool',
            'faceAllowanceExpression' => 'int',
            'active' => 'bool',
        ];
    }
}