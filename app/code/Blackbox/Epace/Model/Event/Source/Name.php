<?php

namespace Blackbox\Epace\Model\Event\Source;

class Name
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('\Blackbox\Epace\Model\Event');


        $events = $model->getCollection();
        $events->getSelect()
        ->columns('name')
        ->group('name');

        foreach ($events as $event) {
            $result[] = $event->getName();
        }

        return $result;
    }
}