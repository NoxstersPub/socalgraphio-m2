<?php
namespace Blackbox\EpaceImport\Block\System\Config\Form\Button;
 
use Magento\Framework\App\Config\ScopeConfigInterface;
 
class CreateJob extends \Magento\Config\Block\System\Config\Form\Field
{
     const BUTTON_TEMPLATE = 'system/config/button/create_job.phtml';
 
		 
	 
	/**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }
        return $this;
    }
	
	 


   /**
     * Render button
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    /**
     * Return ajax url for button
     *
     * @return string
     */
    
     /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        //$originalData = $element->getOriginalData();
        $this->addData(
            [
                'id'        => 'test_create_job_button',
                'button_label'     => __('Create Job')
               // 'onclick'   => 'javascript:check(); return false;'
            ]
        );
        return $this->_toHtml();
    }
}