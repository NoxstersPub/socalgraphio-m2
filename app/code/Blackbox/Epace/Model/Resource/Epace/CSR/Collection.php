<?php
namespace Blackbox\Epace\Model\Resource\Epace\CSR;

/**
 * @method Blackbox_Epace_Model_Epace_CSR[] getItems()
 *
 * Class Blackbox_Epace_Model_Resource_Epace_CSR_Collection
 */

class Collection extends \Blackbox\Epace\Model\Resource\Epace\Collection
{
    protected function _construct()
    {
        $this->_init('efi/cSR');
    }
}