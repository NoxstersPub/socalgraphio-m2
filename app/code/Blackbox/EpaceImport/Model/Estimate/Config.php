<?php

namespace Blackbox\EpaceImport\Model\Estimate;
class Config
{
    /**
     * Retrieve status label
     *
     * @param   string $code
     * @return  string
     */
    public function getStatusLabel($code)
    {
//        $status = Mage::helper('epace/object')
//            ->load('efi/estimate_status', $code);
//        return $status->getDescription();
        return $this->getStatuses()[$code];
    }


    /**
     * Retrieve all statuses
     *
     * @return array
     */
    public function getStatuses()
    {
//        $statuses = Mage::getResourceModel('efi/estimate_status_collection')
//            ->toOptionHash();
        $statuses = [
            '1' => 'Open',
            '2' => 'Converted to Job',
            '3' => 'Customer Submitted',
            '4' => 'Need Info',
            '5' => 'Price Complete',
            '6' => 'Cancelled',
            '7' => 'Re-Quote'
        ];
        return $statuses;
    }
}
