<?php

namespace Blackbox\Epace\Observer;

use Blackbox\Epace\Helper\Epace;

class CompleteOrder implements \Magento\Framework\Event\ObserverInterface {

    const JOB_STATE_ACTIVE = 0;
    const JOB_STATE_COMPLETE = 1;

    protected $_orderStates = array();

    public function execute(\Magento\Framework\Event\Observer $observer) {
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('\Blackbox\Epace\Helper\Epace');

        if (!$helper->isEnabled()) {
            return;
        }

        $messageManager = $objectManager->create('\Magento\Framework\Message\ManagerInterface');
        if (!$helper->isLiveMode()) {
            $messageManager->addSuccess(__("Epace update job method has fired"));
//            Mage::getSingleton('adminhtml/session')->addSuccess('Epace create job method has fired');
            return;
        }
        
        $order = $observer->getEvent()->getOrder(); /* @var Mage_Sales_Model_Order $order */

        if ($order->getState() != \Magento\Sales\Model\Order::STATE_COMPLETE || !empty($order->getEpaceJobId())) {
            return;
        }

        foreach ($order->getInvoiceCollection() as $invoice) {
            /* @var Mage_Sales_Model_Order_Invoice $invoice */

            if (!($job = $invoice->getEpaceJob()) || $invoice->getEpaceJobState() == self::JOB_STATE_COMPLETE) {
                continue;
            }

            $api = $this->_initApi('Close Job');
            $event = $api->getEvent();
            $eventData = array();

            try {
                $result = $api->updateJob($job, \Blackbox\Epace\Helper\Api::JOB_STATUS_CLOSED);

                if ($result['job'] != $job || $result['adminStatus'] != \Blackbox\Epace\Helper\Api::JOB_STATUS_CLOSED) {
                    continue;
                }

                $invoice->setEpaceJobState(self::JOB_STATE_COMPLETE);
                $invoice->getResource()->saveAttribute($invoice, 'epace_job_state');

                $event->setStatus(\Blackbox\Epace\Model\Event::STATUS_SUCCESS);
            } catch (Exception $e) {
                $event->setStatus(\Blackbox\Epace\Model\Event::STATUS_WITH_ERRORS);
                $eventData['error'] = $e->Message();
            }

            $event->setSerializedData(serialize($eventData));
            $event->save();
        }
    }

    /**
     * @param string $eventName
     * @return Blackbox_Epace_Helper_Api
     */
    protected function _initApi($eventName) {
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $api = $objectManager->create('\Blackbox\Epace\Helper\Api');
        $event = $objectManager->create('\Blackbox\Epace\Model\Event')
                        ->setData(array(
                            'name' => $eventName,
                            'processed_time' => time(),
                            'status' => \Blackbox\Epace\Model\Event::STATUS_CRITICAL,
                            'username' => $api->getUsername(),
                            'password' => $api->getPassword(),
                            'host' => $api->getHost(),
                        ));

        $event->save();
        $api->setEvent($event);

        return $api;
    }

    /**
     * @param Mage_Sales_Model_Order_Invoice_Item $item
     * @return bool
     */
    protected function _canExportInvoiceItem($item) {
        return $item->getOrderItem()->getProductType() != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE && $item->getOrderItem()->getRealProductType() != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE;
    }

    protected function getEpaceCountryId($country) {
        $countryMap = [
            'US' => 1,
            'CA' => 2,
            'MX' => 3,
            'AE' => 5,
            'AU' => 17,
            'BB' => 22,
            'BE' => 24,
            'BS' => 34,
            'CL' => 46,
            'CN' => 48,
            'DE' => 56,
            'DK' => 58,
            'FR' => 74,
            'GB' => 76,
            'HK' => 93,
            'IL' => 100,
            'IT' => 107,
            'JP' => 111,
            'KP' => 118,
            'MA' => 134,
            'NL' => 161,
            'PL' => 174,
            'QA' => 182,
            'SG' => 193
        ];

        return $countryMap[$country];
    }

}
