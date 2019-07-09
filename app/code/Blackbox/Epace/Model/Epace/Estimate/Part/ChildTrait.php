<?php
namespace Blackbox\Epace\Model\Epace\Estimate\Part;
trait ChildTrait
{
    /**
     * @return int
     */
    public function getEstimatePartId()
    {
        return $this->getData('estimatePart');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Estimate_Part|false
     */
    public function getEstimatePart()
    {
        return $this->_getObject('estimatePart', 'estimatePart', 'efi/estimate_part');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Estimate_Part $part
     * @return $this
     */
    public function setEstimatePart(\Blackbox\Epace\Model\Epace\Estimate\Part $part)
    {
        return $this->_setObject('estimatePart', $part);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Estimate_Part|false
     * @deprecated
     */
    public function getPart()
    {
        return $this->getEstimatePart();
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Estimate_Part $part
     * @return $this
     * @deprecated
     */
    public function setPart(\Blackbox\Epace\Model\Epace\Estimate\Part $part)
    {
        return $this->setEstimatePart($part);
    }
}