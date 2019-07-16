<?php

namespace Blackbox\EpaceImport\Model\Estimate\Total;

abstract class AbstractMethod extends \Magento\Framework\DataObject
{
    /**
     * Process model configuration array.
     * This method can be used for changing models apply sort order
     *
     * @param   array $config
     * @return  array
     */
    public function processConfigArray($config)
    {
        return $config;
    }
}
