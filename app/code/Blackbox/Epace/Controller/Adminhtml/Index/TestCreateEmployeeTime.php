<?php

namespace Blackbox\Epace\Controller\Adminhtml\Index;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class TestCreateEmployeeTime extends \Magento\Backend\App\Action
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
                'name' => 'Test Create Employee Time',
                'processed_time' => time(),
                'status' => self::STATUS_CRITICAL,
                'username' => $api->getUsername(),
                'password' => $api->getPassword(),
                'host' => $api->getHost(),
            ));
        $event->save();
        $api->setEvent($event);

        $employee = urldecode($this->getRequest()->getParam('employee'));
        $startDate = urldecode($this->getRequest()->getParam('startDate'));
        $startTime = urldecode($this->getRequest()->getParam('startTime'));
        $stopDate = urldecode($this->getRequest()->getParam('stopDate'));
        $stopTime = urldecode($this->getRequest()->getParam('stopTime'));

        try {
            $result = $api->createEmployeeTime($employee, $startDate, $startTime, $stopDate, $stopTime);
            if ($result) {
                $this->messageManager->addSuccess('Employee Time was created successfully');
                $event->setStatus(self::STATUS_SUCCESS);

                $response = '<div>Response:<div><div><ul>';
                foreach ($result as $key => $value) {
                    $response .= '<li>' . $key . ' = ' . $value . '</li>';
                }
                $response .= '</ul></div>';
                $this->messageManager->addSuccess($response);

            } else {
                $this->messageManager->addError('Not valid response');
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