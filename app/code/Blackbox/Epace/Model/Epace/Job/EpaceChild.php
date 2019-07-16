<?php

namespace Blackbox\Epace\Model\Epace\Job;

abstract class EpaceChild extends \Blackbox\Epace\Model\Epace\EpaceObject
{
    /**
     * @return string
     */
    public function getJobId()
    {
        return $this->getData('job');
    }

    /**
     * @return Blackbox_Epace_Model_Epace_Job|false
     */
    public function getJob()
    {
        return $this->_getObject('job', 'job', 'efi/job');
    }

    public function setJob(\Blackbox\Epace\Model\Epace\Job $job)
    {
        return $this->_setObject('job', $job);
    }
}