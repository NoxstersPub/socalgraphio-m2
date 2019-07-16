<?php

namespace Blackbox\Epace\Controller\Adminhtml\Index;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action
{
	
     protected $_apihelper;
    const STATUS_SUCCESS = 'Success';
    const STATUS_WITH_ERRORS = 'With errors';
    const STATUS_CRITICAL = 'Critical';


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
    	
      	 $api->getUsername();

         $resultRedirect = $this->resultRedirectFactory->create();

         $event = $this->_objectManager->create('Blackbox\Epace\Model\Event');
           //die('check');
         $event->setData(array(
                'name' => 'Test Connection',
                'processed_time' => time(),
                'status' => self::STATUS_CRITICAL,  //STATUS_CRITICAL
                'username' => $api->getUsername(),
                'password' => $api->getPassword(),
                'host' => $api->getHost(),
            ));
          $event->save();
         
        $api->setEvent($event);

        $in1 = urldecode($this->getRequest()->getParam('in1'));
        $in2 = urldecode($this->getRequest()->getParam('in2'));

        try {
            $result = $api->findObjects($in1, $in2);
            
            if ($result) {
                $this->messageManager->addSuccess(__('Connection tested successfully.'));
                $event->setStatus(self::STATUS_SUCCESS);
            } else {
             $this->messageManager->addError(__('Not valid response'));

                $event->setStatus(self::STATUS_WITH_ERRORS);
                $event->setSerializedData(serialize(array('error' => 'Not valid response')));
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $event->setStatus(self::STATUS_WITH_ERRORS);
            $event->setSerializedData(serialize(array('error' => $e->getMessage())));
        }
        $event->save();

        $this->_redirect('adminhtml/system_config/edit/section/epace');
        
	}
}

?>

