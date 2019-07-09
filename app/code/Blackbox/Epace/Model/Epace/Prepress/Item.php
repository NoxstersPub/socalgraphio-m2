<?php

namespace Blackbox\Epace\Model\Epace\Prepress;

class Item extends \Blackbox\Epace\Model\Epace\AbstractObject
{
    protected function _construct()
    {
        $this->_init('PrepressItem', 'id');
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'code' => '',
            'prepressType' => '',
            'jdfPrepressType' => '',
            'activityCodeLabor' => '',
            'activityCodeMaterials' => '',
            'description' => '',
            'minColors' => '',
            'maxColors' => '',
            'unitLabel' => '',
            'numFlats' => '',
            'active' => 'bool',
            'jdfDefaultItem' => 'bool',
            'useSizes' => 'bool',
            'sequence' => '',
            'prepressGroup' => '',
            'customerViewable' => 'bool',
        ];
    }
}