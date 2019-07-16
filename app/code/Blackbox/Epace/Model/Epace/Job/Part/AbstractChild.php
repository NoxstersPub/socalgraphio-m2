<?php

namespace Blackbox\Epace\Model\Epace\Job\Part;

use \Blackbox\Epace\Model\Epace\Job\Part\ChildTrait;

abstract class AbstractChild extends \Blackbox\Epace\Model\Epace\Job\EpaceChild
{
    public function getJobPartKeyField()
    {
        return 'JobPartKey';
    }
}