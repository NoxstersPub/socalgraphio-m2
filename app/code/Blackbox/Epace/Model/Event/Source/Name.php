<?php

class Blackbox_Epace_Model_Event_Source_Name
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = array();

        $events = Mage::getModel('epace/event')->getCollection();
        $events->getSelect()
        ->columns('name')
        ->group('name');

        foreach ($events as $event) {
            $result[] = $event->getName();
        }

        return $result;
    }
}