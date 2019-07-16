<?php
namespace Blackbox\Epace\Model\Resource\Epace;
interface CombinedKeyInterface
{
    /**
     * @return string[]
     */
    public function getPrimaryKeyFields();
}