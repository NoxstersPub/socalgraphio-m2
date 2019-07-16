<?php

namespace Blackbox\Epace\Model\System\Config\Source;

class Mode
{
    /**
     * Returns array to be used in packages request type on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label' => 'Test'),
            array('value' => '1', 'label' => 'Live'),
        );
    }
}