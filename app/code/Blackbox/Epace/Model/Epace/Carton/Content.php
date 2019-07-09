<?php

namespace Blackbox\Epace\Model\Epace\Carton;

use \Blackbox\Epace\Model\Epace\Job\Part\ChildTrait;

class Content extends \Blackbox\Epace\Model\Epace\AbstractObject
{
    

    protected function _construct()
    {
        $this->_init('CartonContent', 'id');
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Carton|bool
     */
    public function getCarton()
    {
        return $this->_getObject('carton', 'carton', 'efi/carton');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Carton $carton
     * @return $this
     */
    public function setCarton(\Blackbox\Epace\Model\Epace\Carton $carton)
    {
        return $this->_setObject('carton', $carton);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job|bool
     */
    public function getJob()
    {
        return $this->_getObject('job', 'job', 'efi/job');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Job $job
     * @return $this
     */
    public function setJob(\Blackbox\Epace\Model\Epace\Job $job)
    {
        return $this->_setObject('job', $job);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Product
     */
    public function getJobProduct()
    {
        return $this->_getObject('jobProduct', 'jobProduct', 'efi/job_product');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Job_Product $product
     * @return $this
     */
    public function setJobProduct(\Blackbox\Epace\Model\Epace\Job\Product $product)
    {
        return $this->_setObject('jobProduct', $product);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job|bool
     */
    public function getJobPartJob()
    {
        return $this->_getObject('jobPartJob', 'jobPartJob', 'efi/job');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Job $job
     * @return $this
     */
    public function setJobPartJob(\Blackbox\Epace\Model\Epace\Job $job)
    {
        return $this->_setObject('jobPartJob', $job);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Part|bool
     */
    public function getJobPart()
    {
        return $this->_getObject('jobPart', 'jobPart', 'efi/job_part');
    }

    public function setJobPart(\Blackbox\Epace\Model\Epace\Job\Part $part)
    {
        return $this->_setObject('jobPart', $part);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Material|bool
     */
    public function getJobMaterial()
    {
        return $this->_getObject('jobMaterial', 'jobMaterial', 'efi/job_material');
    }

    public function setJobMaterial(\Blackbox\Epace\Model\Epace\Job\Material $jobMaterial)
    {
        return $this->_setObject('jobMaterial', $jobMaterial);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Part_PressForm|bool
     */
    public function getJobPartPressForm()
    {
        return $this->_getObject('jobPartPressForm', 'jobPartPressForm', 'efi/job_part_pressForm');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Job_Part_PressForm $pressForm
     * @return $this
     */
    public function setJobPartPressForm(\Blackbox\Epace\Model\Epace\Job\Part\PressForm $pressForm)
    {
        return $this->_setObject('jobPartPressForm', $pressForm);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Component|bool
     */
    public function getJobComponent()
    {
        return $this->_getObject('jobComponent', 'jobComponent', 'efi/job_component');
    }

    public function getAssociatedJob()
    {
        if ($this->getJob()) {
            return $this->getJob();
        } else if ($this->getJobPartJob()) {
            return $this->getJobPartJob();
        } else if ($this->getJobProduct()) {
            return $this->getJobProduct()->getJob();
        } else if ($this->getJobMaterial()) {
            return $this->getJobMaterial()->getJob();
        } else if ($this->getJobPartPressForm()) {
            return $this->getJobPartPressForm()->getJob();
        } else if ($this->getJobComponent()) {
            return $this->getJobComponent()->getJob();
        } else if ($this->getJobPartItem()) {
            return $this->getJobPartItem()->getJob();
        } else if ($this->getProof()) {
            return $this->getProof()->getJob();
        } else if ($this->getCarton() && $this->getCarton()->getShipment()) {
            return $this->getCarton()->getShipment()->getJob();
        }
        return false;
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Job_Component $jobComponent
     * @return $this
     */
    public function setJobComponent(\Blackbox\Epace\Model\Epace\Job\Component $jobComponent)
    {
        return $this->_setObject('jobComponent', $jobComponent);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Proof|bool
     */
    public function getProof()
    {
        return $this->_getObject('proof', 'proof', 'efi/proof');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Proof $proof
     * @return $this
     */
    public function setProof(\Blackbox\Epace\Model\Epace\Proof $proof)
    {
        return $this->_setObject('proof', $proof);
    }

    /**
     * @return \Blackbox\Epace\Model\Epace\Job_Part_Item|bool
     */
    public function getJobPartItem()
    {
        return $this->_getObject('jobPartItem', 'jobPartItem', 'efi/job_part_item');
    }

    /**
     * @param \Blackbox\Epace\Model\Epace\Job_Part_Item $item
     * @return $this
     */
    public function setJobPartItem(\Blackbox\Epace\Model\Epace\Job\Part\Item $item)
    {
        return $this->_setObject('jobPartItem', $item);
    }

    public function getDefinition()
    {
        return [
            'id' => 'int',
            'note' => 'string',
            'carton' => 'int',
            'quantity' => 'int',
            'job' => 'string',
            'jobProduct' => 'int',
            'jobPartJob' => 'string',
            'jobPart' => 'string',
            'jobMaterial' => 'int',
            'jobPartPressForm' => 'int',
            'jobComponent' => 'int',
            'proof' => 'int',
            'content' => 'string',
            'JobPartKey' => 'string',
        ];
    }

    public function getJobPartKeyField()
    {
        return 'JobPartKey';
    }
}