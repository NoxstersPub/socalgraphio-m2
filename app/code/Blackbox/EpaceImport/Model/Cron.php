<?php

namespace Blackbox\EpaceImport\Model;
class Cron
{
    /** @var  Blackbox_EpaceImport_Helper_Data */
    protected $helper;

    const XML_PATH_ENABLE = 'epace/import/enable';
    const XML_PATH_LAST_UPDATE_TIME = 'epace/import/last_update_time';
    const XML_PATH_LAST_IMPORT_NEW_TIME = 'epace/import/last_import_new_time';
    const XML_PATH_UPDATE_ESTIMATES = 'epace/import/update_estimates';
    const XML_PATH_UPDATE_JOBS = 'epace/import/update_jobs';
    const XML_PATH_UPDATE_CLOSED_JOBS = 'epace/import/update_closed_jobs';
    const XML_PATH_UPDATE_RECEIVABLES = 'epace/import/update_invoices';
    const XML_PATH_UPDATE_INVOICES = 'epace/import/update_invoices';
    const XML_PATH_UPDATE_SHIPMENTS = 'epace/import/update_shipments';
    const XML_PATH_UPDATE_PURCHASE_ORDERS = 'epace/import/update_purchase_orders';
    const XML_PATH_IMPORT_NEW_OBJECTS = 'epace/import/import_new';
    const XML_PATH_LOG = 'epace/import/log';
    const XML_PATH_MONGO_ENABLE = 'epace/mongo/enable';

    /**
     * @var bool
     */
    protected $logEnabled;

    /**
     * @var Mage_Core_Model_Resource
     */
    protected $resource;
    protected $objectManager;
    /**
     * @var Varien_Db_Adapter_Interface
     */
    protected $connection;

    protected $storeWriter;
            
    protected $entities = [
        'Estimate' => [
            'keys' => [
                'e',
                'estimates'
            ],
            'dateField' => 'entryDate',
            'magentoClass' => 'epacei/estimate',
            'epaceIdField' => 'epace_estimate_id',
        ],
        'Job' => [
            'keys' => [
                'j',
                'jobs'
            ],
            'dateField' => 'dateSetup',
            'magentoClass' => 'sales/order',
            'epaceIdField' => 'epace_job_id',
        ],
        'Invoice' => [
            'keys' => [
                'i',
                'invoices'
            ],
            'dateField' => 'invoiceDate',
            'magentoClass' => 'sales/order_invoice',
            'epaceIdField' => 'epace_invoice_id',
        ],
        'Receivable' => [
            'keys' => [
                'r',
                'receivables'
            ],
            'dateField' => 'invoiceDate',
            'magentoClass' => 'epacei/receivable',
            'epaceIdField' => 'epace_receivable_id'
        ],
        'JobShipment' => [
            'keys' => [
                's',
                'shipments'
            ],
            'dateField' => 'date',
            'magentoClass' => 'sales/order_shipment',
            'epaceIdField' => 'epace_shipment_id',
        ],
        'PurchaseOrder' => [
            'keys' => [
                'po',
                'purchaseOrders'
            ],
            'dateField' => 'dateEntered',
            'magentoClass' => 'epacei/purchaseOrder',
            'epaceIdField' => 'epace_purchase_order_id'
        ],
    ];

    public function __construct()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $objectManager->create('Blackbox\EpaceImport\Helper\Data');
        $this->helper->setOutput(function($message) {
            $this->log($message);
        });
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $this->logEnabled  = $storeManager->getValue(self::XML_PATH_LOG, \Magento\Store\Model\ScopeInterface::SCOPE_STORE,null);
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->storeWriter = $objectManager->create('\Magento\Framework\App\Config\Storage\WriterInterface');
        $this->resource = $objectManager->create('\Magento\Framework\App\ResourceConnection');
        $this->connection = $resource->getConnection();
    }

    public function updateEpaceEntities(\Magento\Cron\Model\Schedule $schedule)
    {
        
        if (ini_get('max_execution_time') < 3600) {
            ini_set('max_execution_time', 3600);
        }
        if (!$this->scopeConfig->getValue(self::XML_PATH_ENABLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return;
        }
        if ($this->scopeConfig->getValue(self::XML_PATH_MONGO_ENABLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            \Blackbox\Epace\Model\Epace\AbstractObject::$useMongo = true;
        }
        $this->log('Start import');
        try {
            $dateTime = $this->getTimeFromConfig(self::XML_PATH_LAST_UPDATE_TIME, '-1 month');

            $currentTime = time();
            
            if ($this->scopeConfig->getValue(self::XML_PATH_UPDATE_RECEIVABLES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $this->log('Updating receivables.');
                $this->updateReceivables($dateTime);
                $this->deleteDeletedEntities('Receivable');
            }
            if ($this->scopeConfig->getValue(self::XML_PATH_UPDATE_ESTIMATES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $this->log('Updating estimates.');
                $this->updateEstimates($dateTime);
                $this->deleteDeletedEntities('Estimate');
            }
            if ($this->scopeConfig->getValue(self::XML_PATH_UPDATE_JOBS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $this->log('Updating jobs.');
                $this->updateJobs($dateTime);
                $this->deleteDeletedEntities('Job');
            }
            if ($this->scopeConfig->getValue(self::XML_PATH_UPDATE_INVOICES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $this->log('Updating invoices.');
                $this->updateInvoices($dateTime);
                $this->deleteDeletedEntities('Invoice');
            }
            if ($this->scopeConfig->getValue(self::XML_PATH_UPDATE_SHIPMENTS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $this->log('Updating shipments.');
                $this->updateShipments($dateTime);
                $this->deleteDeletedEntities('JobShipment');
            }
            if ($this->scopeConfig->getValue(self::XML_PATH_UPDATE_PURCHASE_ORDERS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $this->log('Updating purchase orders.');
                $this->updatePurchaseOrders($dateTime);
                $this->deleteDeletedEntities('PurchaseOrder');
            }
            $this->configWriter->save(self::XML_PATH_LAST_UPDATE_TIME,  $currentTime, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($this->scopeConfig->getValue(self::XML_PATH_IMPORT_NEW_OBJECTS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                return;
            }

            $dateTime = $this->getTimeFromConfig(self::XML_PATH_LAST_IMPORT_NEW_TIME, '-1 month');

            $this->log('Importing new estimates.');
            $this->importNewEstimates($dateTime);
            $this->log('Importing new jobs.');
            $this->importNewJobs($dateTime);
            $this->log('Importing new invoices.');
            $this->importNewInvoices($dateTime);
            $this->log('Importing new shipments.');
            $this->importNewShipments($dateTime);
            $this->log('Importing new purchase orders.');
            $this->importNewPurchaseOrders($dateTime);

            $this->configWriter->save(self::XML_PATH_LAST_IMPORT_NEW_TIME,  $currentTime, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } catch (\Exception $e) {
            $this->log('Exception ' . get_class($e) . ': ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            throw $e;
        }
        
        $this->log('End import');
    }

    protected function getTimeFromConfig($path, $default)
    {
        
        $time = $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!empty($time)) {
            if (is_numeric($time)) {
                $dateTime = new \DateTime();
                $dateTime->setTimestamp($time);
            } else {
                try {
                    $dateTime = new \DateTime($time);
                } catch (\Exception $e) {
                    $this->log($e->getMessage());
                }
            }
        }
        if (!isset($dateTime)) {
            $dateTime = new \DateTime($default);
        }

        return $dateTime;
    }

    protected function importNewEstimates(\DateTime $from)
    {
        /** @var Blackbox_Epace_Model_Resource_Epace_Estimate_Collection $collection */
        
        $collection = $this->objectManager->create('\Blackbox\Epace\Model\Resource\Epace\Estimate');
        if (\Blackbox\Epace\Model\Epace\AbstractObject::$useMongo) {
            $collection->addFilter('_created_at', ['gteq' => $from]);
            $collection->addFilter('entryDate', ['gteq' => new \DateTime('2019-01-01')]);
        } else {
            $collection->addFilter('entryDate', ['gteq' => $from]);
        }

        $ids = $collection->loadIds();
        $count = count($ids);
        $i = 0;
        $this->log('Found ' . $count . ' estimates.');
        foreach ($ids as $estimateId) {
            $this->log('Estimate ' . ++$i . '/' . $count . ': ' . $estimateId);
            /** @var Blackbox_Epace_Model_Epace_Estimate $estimate */
            $estimate = $this->objectManager->create('Blackbox\Epace\Model\Epace\Estimate');
            try {
                $this->importEstimate($estimate);
            } catch (\Exception $e) {
                $this->log($e->getMessage());
            }
        }
    }

    protected function importNewJobs(\DateTime $from)
    {
        /** @var Blackbox_Epace_Model_Resource_Epace_Job_Collection $collection */
        $collection = $this->objectManager->create('Blackbox\Epace\Model\Resource\Epace\Job');
        if (\Blackbox\Epace\Model\Epace\AbstractObject::$useMongo) {
            $collection->addFilter('_created_at', ['gteq' => $from]);
            $collection->addFilter('dateSetup', ['gteq' => new \DateTime('2019-01-01')]);
        } else {
            $collection->addFilter('dateSetup', ['gteq' => $from]);
        }

        /** @var Mage_Core_Model_Resource $resource */
        
        $resource = $this->resource;
        $connection = $this->connection;
        $orderTable = $resource->getTableName('sales_order');

        $ids = $collection->loadIds();
        $count = count($ids);
        $i = 0;
        $this->log('Found ' . $count . ' jobs.');
        foreach ($ids as $jobId) {
            $this->log('Job ' . ++$i . '/' . $count . ': ' . $jobId);
            $select = $connection->select()->from($orderTable, 'count(*)')
                ->where('epace_job_id = ?', $jobId);
            if ($connection->fetchOne($select) > 0) {
                $this->log("Job $jobId already imported.");
            } else  {
                /** @var Blackbox_Epace_Model_Epace_Job $job */
                $job = $this->objectManager->create('Blackbox\Epace\Model\Epace\Job')->load($jobId);
                try {
                    $this->importJob($job, null, false);
                } catch (\Exception $e) {
                    $this->log($e->getMessage());
                }
            }
        }
    }

    protected function importNewShipments(\DateTime $from)
    {
        /** @var Blackbox_Epace_Model_Resource_Epace_Job_Shipment_Collection $collection */
        $collection = $this->objectManager->create('Blackbox\Epace\Model\Resource\Epace\Job\Shipment\Collection');
        if (\Blackbox\Epace\Model\Epace\AbstractObject::$useMongo) {
            $collection->addFilter('_created_at', ['gteq' => $from]);
            $collection->addFilter('date', ['gteq' => new \DateTime('2019-01-01')]);
        } else {
            $collection->addFilter('date', ['gteq' => $from]);
        }

        $ids = $collection->loadIds();
        $count = count($ids);
        $i = 0;
        $this->log('Found ' . $count . ' shipments.');
        foreach ($ids as $id) {
            $this->log('Shipment ' . ++$i . '/' . $count . ': ' . $id);
            /** @var Blackbox_Epace_Model_Epace_Job_Shipment $shipment */
            $shipment = $this->objectManager->create('Blackbox\Epace\Model\Epace\Job\Shipment')->load($id);
            try {
                $this->importShipment($shipment);
            } catch (\Exception $e) {
                $this->log($e->getMessage());
            }
        }
    }

    protected function importNewInvoices(\DateTime $from)
    {
        /** @var Blackbox_Epace_Model_Resource_Epace_Invoice_Collection $collection */
        $collection = $this->objectManager->create('Blackbox\Epace\Model\Resource\Epace\Invoice\Collection');
        if (\Blackbox\Epace\Model\Epace\AbstractObject::$useMongo) {
            $collection->addFilter('_created_at', ['gteq' => $from]);
            $collection->addFilter('invoiceDate', ['gteq' => new \DateTime('2019-01-01')]);
        } else {
            $collection->addFilter('invoiceDate', ['gteq' => $from]);
        }

        $ids = $collection->loadIds();
        $count = count($ids);
        $i = 0;
        $this->log('Found ' . $count . ' invoices.');
        foreach ($ids as $id) {
            $this->log('Invoice ' . ++$i . '/' . $count . ': ' . $id);
            /** @var Blackbox_Epace_Model_Epace_Invoice $invoice */
            $invoice = $this->objectManager->create('Blackbox\Epace\Model\Epace\Invoice')->load($id);
            try {
                $this->importInvoice($invoice);
            } catch (\Exception $e) {
                $this->log($e->getMessage());
            }
        }
    }

    protected function importNewPurchaseOrders(\DateTime $from)
    {
        /** @var Blackbox_Epace_Model_Resource_Epace_Purchase_Order_Collection $collection */
        $collection = $this->objectManager->create('Blackbox\Epace\Model\Resource\Epace\Purchase\Order\Collection');
        if (\Blackbox\Epace\Model\Epace\AbstractObject::$useMongo) {
            $collection->addFilter('_created_at', ['gteq' => $from]);
            $collection->addFilter('dateEntered', ['gteq' => new \DateTime('2019-01-01')]);
        } else {
            $collection->addFilter('dateEntered', ['gteq' => $from]);
        }

        $ids = $collection->loadIds();
        $count = count($ids);
        $i = 0;
        $this->log('Found ' . $count . ' purchase orders.');
        foreach ($ids as $id) {
            $this->log('Purchase Order ' . ++$i . '/' . $count . ': ' . $id);
            /** @var Blackbox_Epace_Model_Epace_Purchase_Order $shipment */
            $purchaseOrder = $this->objectManager->create('Blackbox\Epace\Model\Epace\Purchase\Order')->load($id);
            try {
                $this->importPurchaseOrder($purchaseOrder);
            } catch (\Exception $e) {
                $this->log($e->getMessage());
            }
        }
    }

    public function importEstimate(\Blackbox\Epace\Model\Epace\Estimate $estimate)
    {
        /** @var \Blackbox\EpaceImport\Model_Estimate $magentoEstimate */
        $magentoEstimate = $this->objectManager->create('Blackbox\EpaceImport\Model\Estimate');
        $magentoEstimate->loadByAttribute('epace_estimate_id', $estimate->getId());
        if ($magentoEstimate->getId()) {
            $this->log('Estimate ' . $estimate->getId() . ' already imported.');
            return;
        }

        $this->helper->importEstimate($estimate, $magentoEstimate);
        $string = is_string($magentoEstimate->getCreatedAt()) && !is_numeric($magentoEstimate->getCreatedAt());
        if ($string && strtotime('-10 years') > strtotime($magentoEstimate->getCreatedAt()) || !$string && strtotime('-10 years') > $magentoEstimate->getCreatedAt()) {
            $this->log('[DEBUG] Estimate created_at ' . $magentoEstimate->getCreatedAt() . ' is earlier then 10 years ago');
            $magentoEstimate->unsetData('created_at');
        }
        $magentoEstimate->save();

        $select = $this->connection->select()->from($this->resource->getTableName('epacei_estimate'), 'created_at')
            ->where('entity_id = ?', $magentoEstimate->getId());
        if ($this->connection->fetchOne($select) == '0000-00-00 00:00:00') {
            $this->log('[DEBUG] Estimate created_at saved as 0000-00-00 00:00:00. ' . print_r($estimate->getData(), true) . PHP_EOL . print_r($magentoEstimate->getData()));
        }

        if ($estimate->isConvertedToJob()) {
            $jobs = $estimate->getJobs();
            if (!empty ($jobs)) {
                $count = count($jobs);
                $this->log('Found ' . $count . ' jobs');
                $i = 0;
                foreach ($estimate->getJobs() as $job) {
                    $this->log('Job ' . ++$i . '/' . $count . ': ' . $job->getId());
                    if ($job->getEstimateId() != $estimate->getId()) {
                        $this->log('Job source does match with estimate.');
                        continue;
                    }
                    $this->importJob($job, $magentoEstimate, true);
                }
            }
        }
    }

    public function importJob(\Blackbox\Epace\Model\Epace\Job $job, \Blackbox\EpaceImport\Model\Estimate $magentoEstimate = null, $checkImproted = false)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order');

        if ($checkImproted) {
            $order->loadByAttribute('epace_job_id', $job->getId());
        }

        if ($order->getId()) {
            $this->log('Job ' . $job->getId() . ' already imported');
            return;
        }

        $this->helper->importJob($job, $order, $magentoEstimate);
        $string = is_string($order->getCreatedAt()) && !is_numeric($order->getCreatedAt());
        if ($string && strtotime('-10 years') > strtotime($order->getCreatedAt()) || !$string && strtotime('-10 years') > $order->getCreatedAt()) {
            $this->log('[DEBUG] Order created_at ' . $order->getCreatedAt() . ' is earlier then 10 years ago');
            $order->unsetData('created_at');
        }
        $order->save();

        $select = $this->connection->select()->from($this->resource->getTableName('sales/order'), 'created_at')
            ->where('entity_id = ?', $order->getId());
        if ($this->connection->fetchOne($select) == '0000-00-00 00:00:00') {
            $this->log('[DEBUG] Order created_at saved as 0000-00-00 00:00:00. ' . print_r($job->getData(), true) . PHP_EOL . print_r($order->getData()));
        }

        $i = 0;
        $count = count($job->getInvoices());
        foreach ($job->getInvoices() as $invoice) {
            $i++;
            $this->log("Invoice $i/$count: {$invoice->getId()}");
            try {
                $this->importInvoice($invoice, $order);
            } catch (\Exception $e) {
                $this->log('Error: ' . $e->getMessage());
            }
        }

        $i = 0;
        $count = count($job->getShipments());
        foreach ($job->getShipments() as $shipment) {
            $i++;
            $this->log("Shipment $i/$count: {$shipment->getId()}");
            try {
                $this->importShipment($shipment, $order);
            } catch (\Exception $e) {
                $this->log('Error: ' . $e->getMessage());
            }
        }
    }

    public function importShipment(\Blackbox\Epace\Model\Epace\Job\Shipment $jobShipment, \Magento\Sales\Model\Order $order = null)
    {
        /** @var Mage_Sales_Model_Order_Shipment $orderShipment */
        $orderShipment = $this->objectManager->create('Magento\Sales\Model\Order\Shipment');

        $orderShipment->load($jobShipment->getId(), 'epace_shipment_id');
        if ($orderShipment->getId()) {
            $this->log("Shipment {$jobShipment->getId()} already imported.");
            return;
        }

        $this->helper->importShipment($jobShipment, $order, $orderShipment);
        $orderShipment->save();
    }

    public function importInvoice(\Blackbox\Epace\Model\Epace\Invoice $invoice, \Magento\Sales\Model\Order $order = null)
    {
        /** @var Mage_Sales_Model_Order_Invoice $magentoInvoice */
        $magentoInvoice = $this->objectManager->create('Magento\Sales\Model\Order\Invoice');
        $magentoInvoice->load($invoice->getId(), 'epace_invoice_id');
        if ($magentoInvoice->getId()) {
            $this->log("Invoice {$invoice->getId()} already imported.");
            return;
        }

        $this->helper->importInvoice($invoice, $order, $magentoInvoice);
        $magentoInvoice->save();
//        if ($invoice->getReceivable()) {
//            $this->helper->importReceivable($invoice->getReceivable(), $magentoInvoice)->save();
//        }
    }

    public function importPurchaseOrder(\Blackbox\Epace\Model\Epace\Purchase\Order $purchaseOrder)
    {
        /** @var \Blackbox\EpaceImport\Model_PurchaseOrder $magentoPurchaseOrder */
        $magentoPurchaseOrder = $this->objectManager->create('\Blackbox\EpaceImport\Model\PurchaseOrder');

        $magentoPurchaseOrder->load($purchaseOrder->getId(), 'epace_purchase_order_id');
        if ($magentoPurchaseOrder->getId()) {
            $this->log("Purchase Order {$purchaseOrder->getId()} already imported.");
            return;
        }

        $this->helper->importPurchaseOrder($purchaseOrder, $magentoPurchaseOrder);
        $magentoPurchaseOrder->save();
    }

    protected function updateEstimates(\DateTime $from)
    {
        /** @var \Blackbox\EpaceImport\Model_Resource_Estimate_Collection $collection */
        $collection = $this->objectManager->create('Blackbox\EpaceImport\Model\Resource\Estimate\Collection');

        if (\Blackbox\Epace\Model\Epace\AbstractObject::$useMongo) {
            /** @var Blackbox_Epace_Model_Resource_Epace_Estimate_Collection $mongoCollection */
            $mongoCollection = $this->objectManager->create('Blackbox\Epace\Model\Resource\Epace\Estimate\Collection');
            $mongoCollection->addFilter('_updated_at', ['gt' => $from]);
            $mongoCollection->addFilter('entryDate', ['gteq' => new \DateTime('2019-01-01')]);
            $ids = $mongoCollection->loadIds();
            $collection->addFieldToFilter('epace_estimate_id', ['in' => $ids]);
        } else {
            $collection->addFieldToFilter('epace_estimate_id', ['notnull' => true]);
        }

        $page = 0;
        $collection->setPageSize(100);
        $lastPage = $collection->getLastPageNumber();

        do {
            $page++;

            $collection->clear()->setCurPage($page)->load();
            /** @var \Blackbox\EpaceImport\Model_Estimate $estimate */
            foreach ($collection->getItems() as $estimate) {
                $estimate->setDataChanges(false);
                try {
                    $this->log('Updating estimate ' . $estimate->getId() . '. Epace estimate id: ' . $estimate->getEpaceEstimateId());
                    $this->helper->updateEstimate($estimate, $this->logEnabled);
                } catch (\Exception $e) {
                    $this->log($e->getMessage());
                }
            }
        } while ($page < $lastPage);
    }

    protected function updateJobs(\DateTime $from)
    {
        /** @var Mage_Sales_Model_Resource_Order_Collection $collection */
        $collection = $this->objectManager->create('Magento\Sales\Model\Resource\Order\Collection');
        if (\Blackbox\Epace\Model\Epace\AbstractObject::$useMongo) {
            /** @var Blackbox_Epace_Model_Resource_Epace_Job_Collection $mongoCollection */
            $mongoCollection = $this->objectManager->create('Blackbox\Epace\Model\Resource\Epace\Job\Collection');
            $mongoCollection->addFilter('_updated_at', ['gt' => $from]);
            $mongoCollection->addFilter('dateSetup', ['gteq' => new \DateTime('2019-01-01')]);
            $ids = $mongoCollection->loadIds();
            $collection->addFieldToFilter('epace_job_id', ['in' => $ids]);
        } else {
            $collection->addFieldToFilter('epace_job_id', ['notnull' => true]);
        }
        if ($this->scopeConfig->getValue(self::XML_PATH_UPDATE_CLOSED_JOBS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $collection->addFieldToFilter('state', ['neq' => \Magento\Sales\Model\Order::STATE_CLOSED]);
        }

        $page = 0;
        $collection->setPageSize(100);
        $lastPage = $collection->getLastPageNumber();

        do {
            $page++;

            $collection->clear()->setCurPage($page)->load();
            /** @var Mage_Sales_Model_Order $order */
            foreach ($collection->getItems() as $order) {
                $order->setDataChanges(false);
                try {
                    $this->log('Updating order ' . $order->getId() . '. Job: ' . $order->getEpaceJobId());
                    $this->helper->updateOrder($order, $this->logEnabled);
                } catch (\Exception $e) {
                    $this->log($e->getMessage());
                }
            }

        } while ($page < $lastPage);
    }

    protected function updateReceivables(\DateTime $from)
    {
        /** @var \Blackbox\EpaceImport\Model_Resource_Receivable_Collection $collection */
        $collection = $this->objectManager->create('Blackbox\EpaceImport\Model\Resource\Receivable\Collection');
        if (\Blackbox\Epace\Model\Epace\AbstractObject::$useMongo) {
            /** @var Blackbox_Epace_Model_Resource_Epace_Invoice_Collection $mongoCollection */
            $mongoCollection = $this->objectManager->create('Blackbox\Epace\Model\Resource\Epace\Invoice\Collection');
            $mongoCollection->addFilter('_updated_at', ['gt' => $from]);
            $mongoCollection->addFilter('invoiceDate', ['gteq' => new \DateTime('2019-01-01')]);
            $ids = $mongoCollection->loadIds();
            $collection->addFieldToFilter('epace_receivable_id', ['in' => $ids]);
        } else {
            $collection->addFieldToFilter('epace_receivable_id', ['notnull' => true]);
        }

        $page = 0;
        $collection->setPageSize(100);
        $lastPage = $collection->getLastPageNumber();

        do {
            $page++;

            $collection->clear()->setCurPage($page)->load();
            /** @var \Blackbox\EpaceImport\Model_Receivable $receivable */
            foreach ($collection->getItems() as $receivable) {
                $receivable->setDataChanges(false);
                try {
                    $this->log('Updating receivable ' . $receivable->getId() . '. Epace receivable id: ' . $receivable->getEpaceReceivableId());
                    $this->helper->updateReceivable($receivable, null, $this->logEnabled);
                } catch (\Exception $e) {
                    $this->log($e->getMessage());
                }
            }

        } while ($page < $lastPage);
    }

    protected function updateInvoices(\DateTime $from)
    {
        /** @var Mage_Sales_Model_Entity_Order_Invoice_Collection $collection */
        $collection = $this->objectManager->create('Magento\Sales\Model\Entity\Order\Invoice\Collection');
        if (\Blackbox\Epace\Model\Epace\AbstractObject::$useMongo) {
            /** @var Blackbox_Epace_Model_Resource_Epace_Invoice_Collection $mongoCollection */
            $mongoCollection = $this->objectManager->create('Blackbox\Epace\Model\Resource\Epace\Invoice\Collection');
            $mongoCollection->addFilter('_updated_at', ['gt' => $from]);
            $mongoCollection->addFilter('invoiceDate', ['gteq' => new \DateTime('2019-01-01')]);
            $ids = $mongoCollection->loadIds();
            $collection->addFieldToFilter('epace_invoice_id', ['in' => $ids]);
        } else {
            $collection->addFieldToFilter('epace_invoice_id', ['notnull' => true]);
        }

        $page = 0;
        $collection->setPageSize(100);
        $lastPage = $collection->getLastPageNumber();

        do {
            $page++;

            $collection->clear()->setCurPage($page)->load();
            /** @var Mage_Sales_Model_Order_Invoice $invoice */
            foreach ($collection->getItems() as $invoice) {
                $invoice->setDataChanges(false);
                try {
                    $this->log('Updating invoice ' . $invoice->getId() . '. Epace invoice id: ' . $invoice->getEpaceInvoiceId());
                    $this->helper->updateInvoice($invoice, $this->logEnabled);
                } catch (\Exception $e) {
                    $this->log($e->getMessage());
                }
            }

        } while ($page < $lastPage);
    }

    protected function updateShipments(\DateTime $from)
    {
        /** @var Mage_Sales_Model_Entity_Order_Shipment_Collection $collection */
        $collection = $this->objectManager->create('Magento\Sales\Model\Entity\Order\Shipment\Collection');
        if (\Blackbox\Epace\Model\Epace\AbstractObject::$useMongo) {
            /** @var Blackbox_Epace_Model_Resource_Epace_Job_Shipment_Collection $mongoCollection */
            $mongoCollection = $this->objectManager->create('Blackbox\Epace\Model\Resource\Epace\Job\Shipment\Collection');
            $mongoCollection->addFilter('_updated_at', ['gt' => $from]);
            $mongoCollection->addFilter('date', ['gteq' => new \DateTime('2019-01-01')]);
            $ids = $mongoCollection->loadIds();
            $collection->addFieldToFilter('epace_shipment_id', ['in' => $ids]);
        } else {
            $collection->addFieldToFilter('epace_shipment_id', ['notnull' => true]);
        }

        $page = 0;
        $collection->setPageSize(100);
        $lastPage = $collection->getLastPageNumber();

        do {
            $page++;

            $collection->clear()->setCurPage($page)->load();
            /** @var Mage_Sales_Model_Order_Shipment $shipment */
            foreach ($collection->getItems() as $shipment) {
                $shipment->setDataChanges(false);
                try {
                    $this->log('Updating shipment ' . $shipment->getId() . '. Epace shipment id: ' . $shipment->getEpaceShipmentId());
                    $this->helper->updateShipment($shipment, $this->logEnabled);
                    $shipment->getOrder()->reset();
                    $shipment->dispose();
                    gc_collect_cycles();
                } catch (\Exception $e) {
                    $this->log($e->getMessage());
                }
            }

        } while ($page < $lastPage);
    }

    protected function updatePurchaseOrders(\DateTime $from)
    {
        /** @var \Blackbox\EpaceImport\Model_Resource_PurchaseOrder_Collection $collection */
        $collection = $this->objectManager->create('Blackbox\EpaceImport\Model\Resource\PurchaseOrder\Collection');
        if (\Blackbox\Epace\Model\Epace\AbstractObject::$useMongo) {
            /** @var Blackbox_Epace_Model_Resource_Epace_Purchase_Order_Collection $mongoCollection */
            $mongoCollection = $this->objectManager->create('Blackbox\Epace\Model\Resource\Epace\Purchase\Order\Collection');
            $mongoCollection->addFilter('_updated_at', ['gt' => $from]);
            $mongoCollection->addFilter('dateEntered', ['gteq' => new \DateTime('2019-01-01')]);
            $ids = $mongoCollection->loadIds();
            $collection->addFieldToFilter('epace_purchase_order_id', ['in' => $ids]);
        } else {
            $collection->addFieldToFilter('epace_purchase_order_id', ['notnull' => true]);
        }

        $page = 0;
        $collection->setPageSize(100);
        $lastPage = $collection->getLastPageNumber();

        do {
            $page++;

            $collection->clear()->setCurPage($page)->load();
            /** @var \Blackbox\EpaceImport\Model_PurchaseOrder $purchaseOrder */
            foreach ($collection->getItems() as $purchaseOrder) {
                $purchaseOrder->setDataChanges(false);
                try {
                    $this->log('Updating purchase order ' . $purchaseOrder->getId() . '. Epace purchase order id: ' . $purchaseOrder->getEpacePurchaseOrderId());
                    $this->helper->updatePurchaseOrder($purchaseOrder, $this->logEnabled);
                } catch (\Exception $e) {
                    $this->log($e->getMessage());
                }
            }

        } while ($page < $lastPage);
    }

    public function deleteDeletedEntities($epaceEntity)
    {
        $magentoClass = $this->entities[$epaceEntity]['magentoClass'];
        $epaceIdField = $this->entities[$epaceEntity]['epaceIdField'];

        $this->log('Process deleted entities ' . $epaceEntity);

        $ids = $this->helper->getDeletedIds($epaceEntity, $magentoClass, $epaceIdField);
        if (empty($ids)) {
            $this->log('Not found deleted.');
            return;
        } else {
            $this->log('Found ' . count($ids) . ' deleted entities: ' . print_r($ids, true));
        }

        /** @var Mage_Core_Model_Resource $resource */
        $resource = $this->resource;
        $connection = $this->connection;
        $resourceModel = $this->objectManager->create($magentoClass)->getResource();

        $result = $connection->delete($resourceModel->getMainTable(), [
            $epaceIdField . ' IN (?)' => $ids
        ]);
        $this->log('Deleted ' . $result . ' rows.');
    }

    protected function log($message)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/epace_import_cron.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        if ($this->logEnabled) {
            $logger->info($message);
//            Mage::log($message, null, 'epace_import_cron.log', true);
        }
    }
}