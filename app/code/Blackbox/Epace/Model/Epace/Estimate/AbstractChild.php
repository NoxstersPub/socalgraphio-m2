<?php
namespace Blackbox\Epace\Model\Epace\Estimate;
abstract class AbstractChild extends \Blackbox\Epace\Model\Epace\EpaceObject
{
    /**
     * @return \Blackbox\Epace\Model\Epace\Estimate|false
     */
    public function getEstimate()
    {
        return $this->_getObject('estimate', 'estimate', 'efi/estimate');
    }

    public function setEstimate(\Blackbox\Epace\Model\Epace\Estimate $estimate)
    {
        return $this->_setObject('estimate', $estimate);
    }
}