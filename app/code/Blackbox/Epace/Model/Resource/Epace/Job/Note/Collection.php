<?php
namespace Blackbox\Epace\Model\Resource\Epace\Job\Note;

/**
 * @method Blackbox_Epace_Model_Epace_Job_Note[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_Job_Note_Collection
 */

class Collection extends \Blackbox\Epace\Model\Resource\Epace\Collection
{
    protected function _construct()
    {
        $this->_init('efi/job_note');
    }
}