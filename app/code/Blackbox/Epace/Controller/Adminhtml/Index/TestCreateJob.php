<?php

namespace Blackbox\Epace\Controller\Adminhtml\Index;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class TestCreateJob extends \Magento\Backend\App\Action
{
	
   protected $_apihelper;
   const STATUS_SUCCESS = 'Success';
    const STATUS_WITH_ERRORS = 'With errors';
    const STATUS_CRITICAL = 'Critical';


	public function __construct(
	   \Magento\Backend\App\Action\Context $context,
	   \Blackbox\Epace\Helper\Api $helper
	) {
		 $this->_apihelper = $helper;
		 parent::__construct($context);
		
	}

    public function execute()
    {
    	$api = $this->_apihelper;
        $event = $this->_objectManager->create('Blackbox\Epace\Model\Event');
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

        $customer = urldecode($this->getRequest()->getParam('customer'));
        $description = urldecode($this->getRequest()->getParam('description'));

        try {
 
            $result = $api->createJob($customer, 'TG Test Order '.$customer, array('jobType'=>'7', 'shipToJobContact'=>  $api->getcontactId()));
            if ($result) {
                $this->messageManager->addSuccess(__('Connection tested successfully.'));
                $event->setStatus(self::STATUS_SUCCESS);

                $response = '<div>Response:<div><div><ul>';
                foreach ($result as $key => $value) {
                    $response .= '<li>' . $key . ' = ' . $value . '</li>';
                }
                $response .= '</ul></div>';
                 
                 $this->messageManager->addSuccess($response);

            } else {
                 $this->messageManager->addError(__('Not valid response'));
                $event->setStatus(self::STATUS_WITH_ERRORS);
                $event->setSerializedData(serialize(array('error' => 'Not valid response')));
            }
        } catch (Exception $e) {
             $this->messageManager->addError($e->getMessage());
            $event->setStatus(self::STATUS_WITH_ERRORS);
            $event->setSerializedData(serialize(array('error' => $e->getMessage())));
        }
        $event->save();

        $this->_redirect('adminhtml/system_config/edit/section/epace');
	}
}

?>