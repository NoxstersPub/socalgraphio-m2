<?php

namespace Blackbox\Epace\Model\Epace\Job;

class Contact extends \Blackbox\Epace\Model\Epace\Job\EpaceChild
{
    protected function _construct()
    {
        $this->_init('JobContact', 'id');
    }

    /**
     * @return int
     */
    public function getContactId()
    {
        return $this->getData('contact');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Contact|bool
     */
    public function getContact()
    {
        return $this->_getObject('contact', 'contact', 'efi/contact');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Contact $contact
     * @return $this
     */
    public function setContact(\Blackbox\Epace\Model\Epace\Contact $contact)
    {
        return $this->_setObject('contact', $contact);
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'job' => 'string',
            'contact' => '',
            'billTo' => 'bool',
            'shipTo' => 'bool',
            'contactType' => 'string',
        ];
    }
}