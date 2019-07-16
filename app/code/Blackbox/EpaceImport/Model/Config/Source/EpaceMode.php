<?php
/**
 * My own options
 *
 */
namespace Blackbox\EpaceImport\Model\Config\Source;
class EpaceMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'test', 'label' => __('Test')],
            ['value' => 'live', 'label' => __('Live')]
            
        ];
    }
}
 
?>