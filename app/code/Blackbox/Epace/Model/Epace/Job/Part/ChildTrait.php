<?php

namespace Blackbox\Epace\Model\Epace\Job\Part;

trait ChildTrait
{
    /**
     * @return string
     */
    public function getJobPartNumber()
    {
        return $this->getData('jobPart');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Part|false
     */
    public function getJobPart()
    {
        return $this->_getObject('jobPart', $this->getJobPartKeyField(), 'efi/job_part');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Job_Part $part
     * @return $this
     */
    public function setJobPart(\Blackbox\Epace\Model\Epace\Job\Part $part)
    {
        return $this->_setObject('jobPart', $part);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Part|false
     * @deprecated
     */
    public function getPart()
    {
        return $this->getJobPart();
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Job_Part $part
     * @return $this
     * @deprecated
     */
    public function setPart(\Blackbox\Epace\Model\Epace\Job\Part $part)
    {
        return $this->setJobPart($part);
    }

    /**
     * @return string
     */
    public function getJobPartKey()
    {
        return $this->getData($this->getJobPartKeyField());
    }

    /**
     * @return string
     */
    public abstract function getJobPartKeyField();
}