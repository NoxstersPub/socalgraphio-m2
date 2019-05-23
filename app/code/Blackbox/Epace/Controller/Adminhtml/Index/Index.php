<?php

namespace Blackbox\Epace\Controller\Adminhtml\Index;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action
{
	
   protected $_apihelper;

	public function __construct(
	   \Magento\Framework\App\Action\Context $context,
	   \Blackbox\Epace\Helper\Api $helper
	) {
		 $this->_apihelper = $helper;
		 parent::__construct($context);
		
	}

    public function execute()
    {
    	 $api = $this->_apihelper;
    	
     	echo  '***'.$api->getUsername();
       die(' -->controller');
	}
}

?>