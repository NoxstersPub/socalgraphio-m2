<?php

namespace Blackbox\Epace\Observer;

use Blackbox\Epace\Helper\Epace;

class CreateJob implements \Magento\Framework\Event\ObserverInterface {

    const JOB_STATE_ACTIVE = 0;
    const JOB_STATE_COMPLETE = 1;

    protected $_orderStates = array();

    public function execute(\Magento\Framework\Event\Observer $observer) {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('\Blackbox\Epace\Helper\Epace');

        $displayText = $observer->getData('mp_text');
        echo $displayText->getText() . " - Event </br>";
        $displayText->setText('Execute event successfully.');
        return $this;

        if (!$helper->isEnabled()) {
            return;
        }

        $invoice = $observer->getEvent()->getInvoice(); /* @var Mage_Sales_Model_Order_Invoice $invoice */
        if (!empty($invoice->getEpaceInvoiceId())) {
            return;
        }

        $canExportInvoice = false;
        foreach ($invoice->getAllItems() as $item) {
            if ($this->_canExportInvoiceItem($item)) {
                $canExportInvoice = true;
                break;
            }
        }
        if (!$canExportInvoice) {
            return;
        }
        $messageManager = $objectManager->create('\Magento\Framework\Message\ManagerInterface');
        if (!$helper->isLiveMode()) {
            $messageManager->addSuccess(__("Epace create job method has fired"));
//            Mage::getSingleton('adminhtml/session')->addSuccess('Epace create job method has fired');
            return;
        }

        $api = $this->_initApi('Create Job');
        $event = $api->getEvent();

        try {
            $address = $invoice->getOrder()->getShippingAddress();
//            if ($address->getStorelocatorId()) {
//                //hang at this poin i need this extension to purchase or upgrade.
//                $storelocator = Mage::getModel('storelocator/storelocator')->load($address->getStorelocatorId());
//
//                $customer = $storelocator->getEpaceCustomerId();
//            } else {
//                /** @var Mage_Customer_Model_Address $customerAddress */
////                $customerAddress = Mage::getModel('customer/address')->load($address->getCustomerAddressId());
////                $customer = $customerAddress->getEpaceCustomerId();
////                if (!$customer) {
////                    $result = $api->createObject([
////                        'custName' => $invoice->getOrder()->getCustomerName(),
////                        'address1' => $address->getStreetFull(),
////                        'city' => $address->getCity(),
////                        'customerType' => 1,
////                        'country' => $this->getEpaceCountryId($address->getCountryId()),
////                        'email' => $address->getEmail(),
////                        'faxNumber' => $address->getFax(),
////                        'phoneNumber' => $address->getTelephone(),
////                        'state' => $address->getRegionCode(),
////                        'zip' => $address->getPostcode(),
////                    ], 'customer');
////                }
//            }
            
            if (!$customer) {
                $customer = $this->scopeConfig->getValue('epace/main_settings/default_customer_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE,null);
            }
            $itemsErrors = array();
            $itemIds = array();
            $eventData = array();
            $order = $invoice->getOrder();

            $result = $api->createJob($customer, 'TG Test Order ' . "{$order->getIncrementId()}", ['salesPerson' => $this->scopeConfig->getValue('epace/main_settings/sales_person', \Magento\Store\Model\ScopeInterface::SCOPE_STORE,null)]);
            if (!$result) {
                throw new Exception('Can\t create job: not valid response');
            }

            $jobId = $result['job'];
            $invoice->setEpaceJob($jobId);

            $contacts = $api->findObjects('Contact', "@customer = '$customer' and @active");
            if (!empty($contacts)) {
                $contact = end($contacts);

                $jobContact = $api->createObject([
                    'job' => $jobId,
                    'contact' => $contact
                        ], 'jobContact');

                $api->updateJob($jobId, null, [
                    'jobContact' => $jobContact['id'],
                    'shipToJobContact' => $jobContact['id']
                ]);
            }

            foreach ($invoice->getAllItems() as $item) {
                try {
                    /* @var Mage_Sales_Model_Order_Invoice_Item $item */
                    if (!$this->_canExportInvoiceItem($item)) {
                        continue;
                    }
                    $finalPrice = $item->getRowTotalInclTax() - $item->getDiscountAmount();
                    $itemResult = $api->createJobProduct($jobId, $item->getSku(), $item->getQty(), 1, array(
                        'productValue' => $finalPrice,
                        'amountToInvoice' => $finalPrice
                    ));

                    if (!$itemResult) {
                        throw new Exception('Not valid response');
                    }

                    $itemIds[] = $itemResult['id'];
                } catch (Exception $e) {
                    $itemsErrors[] = 'Can\'t add item ' . $item->getSku() . ': ' . $e->getMessage() . '.';
                }
            }

            $event->setStatus(\Blackbox\Epace\Model\Event::STATUS_SUCCESS);
        } catch (Exception $e) {
            $event->setStatus(\Blackbox\Epace\Model\Event::STATUS_WITH_ERRORS);
            $eventData['error'] = $e->getMessage();
        }

        if (!empty($itemsErrors)) {
            $eventData['item errors'] = implode(' ', $itemsErrors);
        }
        if (!empty($itemIds)) {
            $eventData['created item ids'] = implode(', ', $itemIds);
        }
        $event->setSerializedData(serialize($eventData));
        $event->save();
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
