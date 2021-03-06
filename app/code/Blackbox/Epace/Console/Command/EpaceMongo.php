<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Blackbox\Epace\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Class EpaceMongo
 */
class EpaceMongo extends Command
{
    /**
     * @var MongoDB\Driver\Manager;
     */
    protected $manager;
    protected $database;

    /**
     * @var MongoEpaceCollection[]
     */
    protected $collectionAdapters = [];

    protected $tabs = 0;

    protected $processedEstimates = [];
    protected $processedJobs = [];
    protected $processedShipments = [];
    protected $processedInvoices = [];
    protected $processedReceivables = [];
    
    public static $debug = false;
    protected $configKey = '';

    /**
     * @var Blackbox_EpaceImport_Helper_Data
     */
    protected $helper;
    protected $configWriter;

    /**
     * Date Time Variable added for error 
     * Fatal error: Uncaught Error: Class 'Blackbox\Epace\Console\Command\DateTime' not found 
     */
    protected $timezone;

    const HOST = 'host';
    const DATABASE = 'database';
    const GLOBALS = 'global';
    const ESTIMATES = 'estimates';
    const JOBS = 'jobs';
    const JOBSFILTER = 'jobsFilter';
    const PURCHASEORDERS = 'purchaseOrders';
    const PURCHASEORDERSFILTER = 'pof';
    const INVOICES = 'invoices';
    const INVOICEFILTER = 'invoiceFilter';
    const RECEIVABLES = 'receivables';
    const RECEIVABLESFILTER = 'receivablesFilter';
    const SHIPMENTS = 'shipments';
    const SHIPMENTSFILTER = 'shipmentsFilter';
    const FROM = 'from';
    const TO = 'to';
    const BULKWRITELIMIT = 'bulkWriteLimit';
    const RESAVEENTITIES = 'resaveEngities';
    const DATES = 'dates';
    const CONFIGKEY = 'key';
    const CONFIGSETTINGS = 'configSettings';
    const MODE = 'mode';
    const NOTIMPORTED = 'notImported';
    const NOMONGOFILTER = 'noMongoFilter';
    const PRINTDELETEDENTITIES = 'printDeletedEntities';
    const DEBUG = "debug";

    public function __construct(WriterInterface $configWriter, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone)
    {
        parent::__construct();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $objectManager->create('\Blackbox\Epace\Helper\Epace');
        
        $this->configWriter = $configWriter;
        $this->timezone = $timezone;
    }

    protected function configure()
    {
        $this->addOption(
                self::HOST,
                null,
                InputOption::VALUE_OPTIONAL,
		'Host'
                );
        $this->addOption(
                self::DATABASE,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Database'
                );
        $this->addOption(
                self::GLOBALS,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Globals',
                0
                );
        $this->addOption(
                self::ESTIMATES,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Import Estimates',
                0
                );
        $this->addOption(
                self::SHIPMENTS,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Import Estimates',
                0
                );
        $this->addOption(
                self::SHIPMENTSFILTER,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Filter shipments by',
                0
                );
        $this->addOption(
                self::JOBS,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Import Jobs',
                0
                );
        $this->addOption(
                self::JOBSFILTER,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Filter Jobs by',
                0
                );
        $this->addOption(
                self::INVOICES,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Import Invoices',
                0
                );
        $this->addOption(
                self::INVOICEFILTER,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Filter Invoices by',
                0
                );
        $this->addOption(
                self::RECEIVABLES,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Import Receivables',
                0
                );
        $this->addOption(
                self::RECEIVABLESFILTER,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Filter RF',
                0
                );
        $this->addOption(
                self::PURCHASEORDERS,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Import PurchaseOrders',
                0
                );
        $this->addOption(
                self::PURCHASEORDERSFILTER,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Filter PurchaseOrders by',
                0
                );
        $this->addOption(
                self::FROM,
                null,   
                InputOption::VALUE_OPTIONAL,
		'From',
                0
                );
        $this->addOption(
                self::TO,
                null,   
                InputOption::VALUE_OPTIONAL,
		'To',
                0
                );
        $this->addOption(
                self::BULKWRITELIMIT,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Bulk Write Limit',
                1
                );
        $this->addOption(
                self::RESAVEENTITIES,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Resave entities',
                1
                );
        $this->addOption(
                self::DATES,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Resave dates',
                1
                );
        $this->addOption(
                self::CONFIGKEY,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Key'
                );
        $this->addOption(
                self::CONFIGSETTINGS,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Config Settings'
                );
        $this->addOption(
                self::MODE,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Import Mode'
                );
        $this->addOption(
                self::DEBUG,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Debug Mode'
                );
        $this->addOption(
                self::NOTIMPORTED,
                null,   
                InputOption::VALUE_OPTIONAL,
		'not Imported'
                );
        $this->addOption(
                self::NOMONGOFILTER,
                null,   
                InputOption::VALUE_OPTIONAL,
		'No Mongo Filter'
                );
        $this->addOption(
                self::PRINTDELETEDENTITIES,
                null,   
                InputOption::VALUE_OPTIONAL,
		'Print Deleted Entities'
                );
        
        $this->setName('epace:import')->setDescription('Epace Mongo Console Command');
        
        parent::configure();
    }
    
    /**Commented on will**/
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        error_reporting(E_ALL);
        
        if ( $input->getOption(self::DEBUG) ) {
            \Blackbox\Epace\Console\Command\EpaceMongoDebug::$debug = true;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        if ( $input->getOption(self::CONFIGKEY) ) {
            $this->configKey = $input->getOption(self::CONFIGKEY);
        }
        
        $this->saveStatus('running');

        try {
            try {
                if ( $input->getOption(self::CONFIGSETTINGS) ) {
                    /** @var Blackbox_Epace_Helper_Mongo $helper */
                    $helper = $objectManager->create('\Blackbox\Epace\Helper\Mongo');
                    $this->manager = new \MongoDB\Driver\Manager($helper->getHost());
                    $this->database = $helper->getDatabase();
                } else {
                    $host = $input->getOption(self::HOST);
                    $this->manager = new \MongoDB\Driver\Manager($host);

                    $this->database = $input->getOption(self::DATABASE);
                }
                if (!$this->database) {
                    throw new \Exception('No database specified.');
                }

                if ( $input->getOption(self::BULKWRITELIMIT) ) {
                    \Blackbox\Epace\Console\Command\MongoEpaceCollection::$bulkWriteLimit = (int)$input->getOption(self::BULKWRITELIMIT);
                }

                if ($mode = $input->getOption(self::MODE)) {
                    switch ($mode) {
                        case 'notImported':
                            $this->listNotImported($input, $output);
                            break;
                        case 'fixDates':
                            $this->fixDates();
                            break;
                        case 'vendors':
                            $this->importVendors();
                            break;
                        case 'resave':
                            $this->resaveEntities($input, $output);
                            break;
                        case 'delete':
                            $this->deleteEntities();
                            break;
                        case 'listDeleted':
                            $this->printDeletedEntities($input, $output);
                            break;
                        default:
                            throw new \Exception('Unsupported mode. Allowed values: notImported, fixDats, vendors, resave, delete, listDeleted');
                    }
                    return;
                }

                $this->importToMongo($input, $output);
                
            } finally {
                foreach ($this->collectionAdapters as $adapter) {
                    try {
                        $adapter->flush();
                    } catch (\Exception $e) {
                        $this->writeln('Error while flushing ' . $adapter->getCollectionName() . ': ' . $e->getMessage());
                    }
                }
            }

            $this->saveStatus('success');
        } catch (\Exception $e) {
            $this->writeln('Error: ' . $e->getMessage());
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/epacemongo.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($e);
            $this->saveStatus('error', 'Exception in ' . $e->getFile() . ':' . $e->getLine() . '. Message: ' . $e->getMessage());
        }
    }
    
    protected function saveStatus($status, $message = '')
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        
        if ( $this->configKey ) {
            $this->configWriter->save('/epace_import/mongo/' . $this->configKey, json_encode([
                'time' => time(),
                'status' => $status,
                'message' => $message
            ]));
        }
    }

    public function importToMongo(InputInterface $input, OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        
        $from = $input->getOption(self::FROM);
        $to = $input->getOption(self::TO);

        /**
         * Commented on pupose, commented Classes needs to be mapped
         */
        if ( $input->getOption(self::GLOBALS) ) {
            $this->importEntities('SalesPerson');
            $this->importEntities('SalesCategory');
            $this->importEntities('SalesTax');
            // $this->importEntities('CSR');
            $this->importEntities('ShipmentType');
            $this->importEntities('Ship\Via');
            $this->importEntities('Country');
            $this->importEntities('Estimate\Status');
            $this->importEntities('Job\Status');
            $this->importEntities('Job\Type');
            $this->importEntities('Invoice\Extra\Type');
            $this->importEntities('Purchase\Order\Type');
            $this->importEntities('POStatus');
        }

        if ( $input->getOption(self::ESTIMATES) ) {
            /** @var Blackbox_Epace_Model_Resource_Epace_Estimate_Collection $collection */
            $collection = $objectManager->create('\Blackbox\Epace\Model\Resource\Epace\Estimate\Collection');

            /**
             * The date time function is not for Magento
             * The "-3 Months 00:00Am was not readable by the funcation so I am using default value for debugging"
             */

            if ($from) {
                $collection->addFilter('entryDate', ['gteq' => $this->timezone->date("2017-01-20T13:59:19+03:00")]);
            }
            if ($to) {
                $collection->addFilter('entryDate', ['lteq' => new DateTime($to)]);
            }
            $collection->setOrder('entryDate', 'ASC');

            /**
             * EF mapping needs to be added
             * Fatal error: Uncaught Error: Undefined class constant 'EF'
             */
            /**
             * Commenting it for debugging
             */
            // if ( $input->getOption(self::EF) ) {
            //     $this->addFilter( $collection, $input->getOption(self::EF) );
            // }

            $ids = $collection->loadIds();
            $count = count($ids);

            if ( $input->getOption(self::NOTIMPORTED) ) {
                $importedIds = $this->getCollectionAdapter('estimate')->loadIds();
                foreach ($ids as $key => $id) {
                    if (in_array($id, $importedIds)) {
                        unset($ids[$key]);
                    }
                }

                $oldCount = $count;
                $count = count($ids);

                $this->writeln('Found ' . $count . ' (' . $oldCount . ') estimates');
            } else {
                $this->writeln('Found ' . $count . ' estimates.');
            }

            $i = 0;
            foreach ($ids as $estimateId) {
                $this->writeln('Estimate ' . ++$i . '/' . $count . ': ' . $estimateId);
                /** @var Blackbox_Epace_Model_Epace_Estimate $estimate */
                $estimate = $objectManager->create('\Blackbox\Epace\Model\Epace\Estimate')->load($estimateId);
                $this->importEstimate($estimate);
            }
        }

        if ( $input->getOption(self::JOBS) ) {
            /** @var Blackbox_Epace_Model_Resource_Epace_Job_Collection $collection */
            $collection = $objectManager->create('\Blackbox\Epace\Model\Resource\Epace\Job\Collection');

            /**
             * This piece of code needs to be update
             */
            if ($from) {
                $collection->addFilter('dateSetup', ['gteq' => $this->timezone->date("2017-01-20T13:59:19+03:00")]);
            }
            if ($to) {
                $collection->addFilter('dateSetup', ['lteq' => new DateTime($to)]);
            }
            $collection->setOrder('dateSetup', 'ASC');

            if ( $input->getOption(self::JOBSFILTER) ) {
                $this->addFilter( $collection, $input->getOption(self::JOBSFILTER) );
            }

            $ids = $collection->loadIds();
            $count = count($ids);

            if ( $input->getOption(self::NOTIMPORTED) ) {
                $importedIds = $this->getCollectionAdapter('job')->loadIds();
                foreach ($ids as $key => $id) {
                    if (in_array($id, $importedIds)) {
                        unset($ids[$key]);
                    }
                }

                $oldCount = $count;
                $count = count($ids);

                $this->writeln('Found ' . $count . ' (' . $oldCount . ') jobs');
            } else {
                $this->writeln('Found ' . $count . ' jobs.');
            }

            $i = 0;
            $this->tabs++;
            try {
                foreach ($ids as $jobId) {
                    $this->writeln('Job ' . ++$i . '/' . $count . ': ' . $jobId);
                    if (in_array($jobId, $this->processedJobs)) {
                        $this->writeln("\tJob $jobId already processed.");
                    } else {
                        /** @var Blackbox_Epace_Model_Epace_Job $job */
                        $job = $objectManager->create('\Blackbox\Epace\Model\Epace\Job')->load($jobId);

                        if ($job->getEstimate()) {
                            $this->tabs++;
                            try {
                                $this->writeln('Import estimate ' . $job->getEstimate()->getId());
                                $this->importEstimate($job->getEstimate());
                                if ($job->getEstimate()->isConvertedToJob()) {
                                    continue;
                                } else {
                                    $this->writeln('Estimate ' . $job->getEstimate()->getId() . ' is not converted to job.');
                                }
                            } finally {
                                $this->tabs--;
                            }
                        }
                        $this->importJob($job);
                    }
                }
            } finally {
                $this->tabs--;
            }
        }

        if ( $input->getOption(self::INVOICES) ) {
            /** @var Blackbox_Epace_Model_Resource_Epace_Invoice_Collection $collection */
            $collection = $objectManager->create('\Blackbox\Epace\Model\Resource\Epace\Invoice\Collection');
            if ($from) {
                $collection->addFilter('invoiceDate', ['gteq' => new DateTime($from)]);
            }
            if ($to) {
                $collection->addFilter('invoiceDate', ['lteq' => new DateTime($to)]);
            }
            $collection->setOrder('invoiceDate', 'ASC');

            if ( $input->getOption(self::INVOICEFILTER) ) {
                $this->addFilter( $collection, $input->getOption(self::INVOICEFILTER) );
            }

            $ids = $collection->loadIds();
            $count = count($ids);

            if ( $input->getOption(self::NOTIMPORTED) ) {
                $importedIds = $this->getCollectionAdapter('invoice')->loadIds();
                foreach ($ids as $key => $id) {
                    if (in_array($id, $importedIds)) {
                        unset($ids[$key]);
                    }
                }

                $oldCount = $count;
                $count = count($ids);

                $this->writeln('Found ' . $count . ' (' . $oldCount . ') invoices');
            } else {
                $this->writeln('Found ' . $count . ' invoices.');
            }

            $i = 0;
            $this->tabs++;
            try {
                foreach ($ids as $id) {
                    $this->writeln('Invoice ' . ++$i . '/' . $count . ': ' . $id);
                    if (array_key_exists($id, $this->processedInvoices)) {
                        $this->writeln("\tInvoice $id already processed.");
                    } else {
                        /** @var Blackbox_Epace_Model_Epace_Invoice $invoice */
                        $invoice = $objectManager->create('\Blackbox\Epace\Model\Epace\Invoice')->load($id);
                        $this->importInvoice($invoice);
                    }
                }
            } finally {
                $this->tabs--;
            }
        }

        if ( $input->getOption(self::RECEIVABLES) ) {
            /** @var Blackbox_Epace_Model_Resource_Epace_Receivable_Collection $collection */
            $collection = $objectManager->create('\Blackbox\Epace\Model\Resource\Epace\Receivable\Collection');
            if ($from) {
                $collection->addFilter('dateSetup', ['gteq' => new DateTime($from)]);
            }
            if ($to) {
                $collection->addFilter('dateSetup', ['lteq' => new DateTime($to)]);
            }
            $collection->setOrder('dateSetup', 'ASC');

            if ( $input->getOption(self::RECEIVABLESFILTER) ) {
                $this->addFilter( $collection, $input->getOption(self::RECEIVABLESFILTER) );
            }

            $ids = $collection->loadIds();
            $count = count($ids);

            if ( $input->getOption(self::NOTIMPORTED) ) {
                $importedIds = $this->getCollectionAdapter('receivable')->loadIds();
                foreach ($ids as $key => $id) {
                    if (in_array($id, $importedIds)) {
                        unset($ids[$key]);
                    }
                }

                $oldCount = $count;
                $count = count($ids);

                $this->writeln('Found ' . $count . ' (' . $oldCount . ') receivables');
            } else {
                $this->writeln('Found ' . $count . ' receivables.');
            }

            $i = 0;
            $this->tabs++;
            try {
                foreach ($ids as $id) {
                    $this->writeln('Receivable ' . ++$i . '/' . $count . ': ' . $id);
                    if (array_key_exists($id, $this->processedReceivables)) {
                        $this->writeln("\tReceivable $id already processed.");
                    } else {
                        /** @var Blackbox_Epace_Model_Epace_Receivable $receivable */
                        $receivable = $objectManager->create('\Blackbox\Epace\Model\Epace\Receivable')->load($id);
                        $this->importReceivable($receivable);
                    }
                }
            } finally {
                $this->tabs--;
            }
        }

        if ( $input->getOption(self::SHIPMENTS) ) {
            /** @var Blackbox_Epace_Model_Resource_Epace_Job_Shipment_Collection $collection */
            $collection = $objectManager->create('\Blackbox\Epace\Model\Resource\Epace\Job\Shipment\Collection');
            if ($from) {
                $collection->addFilter('date', ['gteq' => new DateTime($from)]);
            }
            if ($to) {
                $collection->addFilter('date', ['lteq' => new DateTime($to)]);
            }
            $collection->setOrder('date', 'ASC');

            if ( $input->getOption(self::SHIPMENTSFILTER) ) {
                $this->addFilter( $collection, $input->getOption(self::SHIPMENTSFILTER) );
            }

            $ids = $collection->loadIds();
            $count = count($ids);

            if ( $input->getOption(self::NOTIMPORTED) ) {
                $importedIds = $this->getCollectionAdapter('job_shipment')->loadIds();
                foreach ($ids as $key => $id) {
                    if (in_array($id, $importedIds)) {
                        unset($ids[$key]);
                    }
                }

                $oldCount = $count;
                $count = count($ids);

                $this->writeln('Found ' . $count . ' (' . $oldCount . ') shipments');
            } else {
                $this->writeln('Found ' . $count . ' shipments.');
            }

            $i = 0;
            $this->tabs++;
            try {
                foreach ($ids as $id) {
                    $this->writeln('JobShipment ' . ++$i . '/' . $count . ': ' . $id);
                    if (array_key_exists($id, $this->processedShipments)) {
                        $this->writeln("\tJobShipment $id already processed.");
                    } else {
                        /** @var Blackbox_Epace_Model_Epace_Job_Shipment $shipment */
                        $shipment = $objectManager->create('\Blackbox\Epace\Model\Resource\Epace\Job\Shipment\Collection')->load($id);
                        $this->importShipment($shipment);
                    }
                }
            } finally {
                $this->tabs--;
            }
        }

        if ( $input->getOption(self::PURCHASEORDERS) ) {
            /** @var Blackbox_Epace_Model_Resource_Epace_Purchase_Order_Collection $collection */
            $collection = $objectManager->create('\Blackbox\Epace\Model\Resource\Epace\Purchase\Order\Collection');
            if ($from) {
                $collection->addFilter('dateEntered', ['gteq' => $this->timezone->date("2017-01-20T13:59:19+03:00")]);
            }
            if ($to) {
                $collection->addFilter('dateEntered', ['lteq' => $this->timezone->date("2019-01-20T13:59:19+03:00")]);
            }
            $collection->setOrder('dateEntered', 'ASC');

            if ( $input->getOption(self::PURCHASEORDERSFILTER) ) {
                $this->addFilter( $collection, $input->getOption(self::PURCHASEORDERSFILTER) );
            }

            $ids = $collection->loadIds();
            $count = count($ids);

            if ($input->getOption(self::NOTIMPORTED) ) {
                $importedIds = $this->getCollectionAdapter('purchase_order')->loadIds();
                foreach ($ids as $key => $id) {
                    if (in_array($id, $importedIds)) {
                        unset($ids[$key]);
                    }
                }

                $oldCount = $count;
                $count = count($ids);

                $this->writeln('Found ' . $count . ' (' . $oldCount . ') purchase orders');
            } else {
                $this->writeln('Found ' . $count . ' purchase orders.');
            }

            $i = 0;
            $this->tabs++;
            try {
                foreach ($ids as $id) {
                    $this->writeln('PurchaseOrder ' . ++$i . '/' . $count . ': ' . $id);
                    /** @var Blackbox_Epace_Model_Epace_Purchase_Order $purchaseOrder */
                    $purchaseOrder = $objectManager->create('\Blackbox\Epace\Model\Epace\Purchase\Order')->load($id);
                    $this->importPurchaseOrder($purchaseOrder);
                }
            } finally {
                $this->tabs--;
            }
        }
    }

    /**
     * @param Blackbox_Epace_Model_Resource_Epace_Collection $collection
     * @param string|array|object $filters
     * @throws Exception
     */
    protected function addFilter(\Blackbox\Epace\Model\Resource\Epace\Collection $collection, $filters)
    {
        if (is_string($filters)) {
            $filters = json_decode($filters);
        }
        if (is_null($filters)) {
            throw new \Exception("Invalid {$collection->getResource()->getObjectType()} filter");
        }
        if (!is_array($filters)) {
            $filters = [$filters];
        }
        foreach ($filters as $filter) {
            if (is_object($filter->value)) {
                $filter->value = (array)$filter->value;
            }
            $collection->addFilter($filter->field, $filter->value);
        }
    }

    public function importVendors()
    {
        
        /** @var Blackbox_Epace_Model_Resource_Epace_Vendor_Collection $collection */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objectManager->create('\Blackbox\Epace\Model\Resource\Epace\Vendor\Collection');
        $ids = $collection->loadIds();
        $count = count($ids);
        $i = 0;

        $adapter = $this->getCollectionAdapter('vendor');

        foreach ($ids as $id) {
            $this->writeln(++$i . '/' . $count);
            $vendor = $objectManager->create('\Blackbox\Epace\Model\Epace\Vendor')->load($id);
            if ($vendor->getId()) {
                $adapter->insertOrUpdate($vendor);
            } else {
                $this->writeln('Error: could not load.');
            }
        }
    }

    public function resaveEntities(InputInterface $input, OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        
        \Blackbox\Epace\Model\Epace\EpaceObject::$useMongo = true;

        $entities = array_filter(explode(',', $input->getOption(self::RESAVEENTITIES)));
        if (empty($entities)) {
            $this->writeln('Error: entities are empty.');
            return;
        }

        foreach ($entities as $entity) {
            $adapter = $this->getCollectionAdapter($entity);
            /** @var Blackbox_Epace_Model_Resource_Epace_Collection $collection */
            $collection = $objectManager->create('\Blackbox\Epace\Model\Resource\Epace\\'.$entity.'\Collection');
            if (!$adapter || !$collection) {
                $this->writeln('Error: invalid entity ' . $entity);
            }
            $this->writeln($adapter->getCollectionName());

            $ids = $collection->loadIds();
            foreach ($ids as $id) {
                $this->writeln($id);
                $object = $objectManager->create('\Blackbox\Epace\Model\Epace\\'.$entity)->load($id);
                $adapter->updateDataRaw($object->getData());
            }
        }

        $this->writeln('Success.');
    }

    protected function fixDates()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $collections = $this->manager->executeCommand($this->database, new \MongoDB\Driver\Command(['listCollections' => 1, 'nameOnly' => true]));
        /** @var Blackbox_Epace_Helper_Data $helper */
        $helper = $objectManager->create('\Blackbox\Epace\Helper\Epace');
        foreach ($collections as $collection) {
            $type = $helper->getTypeName($collection->name);
            if ($type) {
                $this->writeln($collection->name);
                $this->fixCollectionDates($type);
            } else {
                $this->writeln('Object type for collection ' . $collection->name . ' not found');
            }
        }
    }

    protected function fixCollectionDates($type)
    {
        \Blackbox\Epace\Model\Epace\EpaceObject::$useMongo = true;
        $adapter = $this->getCollectionAdapter($type);

        $definition = $adapter->getResource()->getDefinition();
        $dateFields = array_keys(array_filter($definition, function($value) {
            return $value == 'date';
        }));

        $ids = $adapter->loadIds();
        $count = count($ids);
        $i = 0;
        foreach ($ids as $id) {
            $this->write(++$i . '/' . $count . ' ' . $id);
            $data = $adapter->loadData($id);
            if (!$data) {
                $this->writeln('not found');
                continue;
            }

            $updated = false;
            foreach ($dateFields as $field) {
                if (!array_key_exists($field, $data) || is_null($data[$field]) || $data[$field] instanceof MongoDB\BSON\UTCDateTime) {
                    continue;
                }

                if (is_string($data[$field]) && !is_numeric($data[$field])) {
                    $data[$field] = new MongoDB\BSON\UTCDateTime(strtotime($data[$field]) *1000);
                } else {
                    $data[$field] = new MongoDB\BSON\UTCDateTime((int)$data[$field] * 1000);
                }
                $updated = true;
            }

            if (!isset($data['_created_at'])) {
                $data['_created_at'] = new MongoDB\BSON\UTCDateTime(time() * 1000);
                $data['_updated_at'] = $data['_created_at'];
                $updated = true;
            } else if (!isset($data['_updated_at'])) {
                $data['_updated_at'] = $data['_created_at'];
                $updated = true;
            }

            if ($updated) {
                $this->writeln(' update');
                $adapter->updateDataRaw($data);
            } else {
                $this->writeln('');
            }
        }

        $adapter->flush();
    }

    protected function importEstimate(\Blackbox\Epace\Model\Epace\Estimate $estimate)
    {
        if (in_array($estimate->getId(), $this->processedEstimates)) {
            $this->writeln('Estimate ealready processed');
        } else {
            $forceUpdate = false;
            foreach ($estimate->getProducts() as $product) {
                $forceUpdate |= $this->getCollectionAdapter('estimate_product')->insertOrUpdate($product);
                foreach ($product->getParts() as $part) {
                    $forceUpdate |= $this->getCollectionAdapter('estimate_part')->insertOrUpdate($part);
                    foreach ($part->getSizeAllowances() as $sizeAllowance) {
                        $forceUpdate |= $this->getCollectionAdapter('estimate_part_sizeAllowance')->insertOrUpdate($sizeAllowance);
                    }
                    foreach ($part->getQuantities() as $quantity) {
                        $forceUpdate |= $this->getCollectionAdapter('estimate_quantity')->insertOrUpdate($quantity);
                    }
                }
                foreach ($product->getPriceSummaries() as $priceSummary) {
                    $forceUpdate |= $this->getCollectionAdapter('estimate_product_priceSummary')->insertOrUpdate($priceSummary);
                }
            }
            foreach ($estimate->getQuoteLetters() as $quoteLetter) {
                $forceUpdate |= $this->getCollectionAdapter('estimate_quoteLetter')->insertOrUpdate($quoteLetter);
                foreach ($quoteLetter->getNotes() as $note) {
                    $forceUpdate |= $this->getCollectionAdapter('estimate_quoteLetter_note')->insertOrUpdate($note);
                }
            }
            if ($estimate->getCustomer()) {
                $this->getCollectionAdapter('customer')->insertOrUpdate($estimate->getCustomer());
            }

            if (\Blackbox\Epace\Console\Command\EpaceMongoDebug::$debug) {
                if (!$estimate->getEntryDate() || !$estimate->getEntryTime()) {
                    $this->writeln('[DEBUG] Estimate entry date or time is empty. ' . print_r($estimate->getData(), true));
                } else {
                    $yearAgo = strtotime('-1 year');
                    if (strtotime($estimate->getEntryDate()) + strtotime($estimate->getEntryTime()) < $yearAgo) {
                        $this->writeln('[DEBUG] Estimate entry datetime is earlier then a year. ' . print_r($estimate->getData(), true));
                    }
                }
            }

            $this->getCollectionAdapter('estimate')->insertOrUpdate($estimate, $forceUpdate);

            $this->processedEstimates[] = $estimate->getId();
        }
//        if ($estimate->getSalesPerson()) {
//            $this->getCollectionAdapter('salesPerson')->insertOrUpdate($estimate->getSalesPerson());
//        }
//        if ($estimate->getCSR()) {
//            $this->getCollectionAdapter('cSR')->insertOrUpdate($estimate->getCSR());
//        }
//        if ($estimate->getStatus()) {
//            $this->getCollectionAdapter('estimate_status')->insertOrUpdate($estimate->getStatus());
//        }

        if ($estimate->isConvertedToJob()) {
            $jobs = $estimate->getJobs();
            if (!empty ($jobs)) {
                $count = count($jobs);
                $this->writeln('Found ' . $count . ' jobs');
                $i = 0;
                $this->tabs++;
                try {
                    foreach ($estimate->getJobs() as $job) {
                        $this->writeln('Job ' . ++$i . '/' . $count . ': ' . $job->getId());
                        if ($job->getEstimateId() != $estimate->getId()) {
                            $this->writeln('Job source does match with estimate.');
                            continue;
                        }
                        $this->importJob($job);
                    }
                } finally {
                    $this->tabs--;
                }
            }
        }
    }

    protected function importJob(\Blackbox\Epace\Model\Epace\Job $job)
    {
        if (in_array($job->getId(), $this->processedJobs)) {
            $this->writeln('Job already processed');
            return;
        }
        $forceUpdate = false;
//        if ($job->getAdminStatus()) {
//            $this->getCollectionAdapter('job_status')->insertOrUpdate($job->getAdminStatus());
//        }
//        if ($job->getPrevAdminStatus()) {
//            $this->getCollectionAdapter('job_status')->insertOrUpdate($job->getPrevAdminStatus());
//        }
        if ($job->getCustomer()) {
            $this->getCollectionAdapter('customer')->insertOrUpdate($job->getCustomer());
        }
//        if ($job->getCSR()) {
//            $this->getCollectionAdapter('cSR')->insertOrUpdate($job->getCSR());
//        }
//        if ($job->getSalesPerson()) {
//            $this->getCollectionAdapter('salesPerson')->insertOrUpdate($job->getSalesPerson());
//        }
        if ($job->getQuote()) {
            $this->getCollectionAdapter('quote')->insertOrUpdate($job->getQuote());
        }
        foreach($job->getProducts() as $product) {
            $forceUpdate |= $this->getCollectionAdapter('job_product')->insertOrUpdate($product);
        }
//        $this->importShipVia($job->getShipVia());
        foreach ($job->getParts() as $part) {
            $forceUpdate |= $this->getCollectionAdapter('job_part')->insertOrUpdate($part);
            foreach ($part->getMaterials() as $material) {
                $forceUpdate |= $this->getCollectionAdapter('job_material')->insertOrUpdate($material);
            }
            foreach ($part->getPrePressOps() as $prePressOp) {
                $forceUpdate |= $this->getCollectionAdapter('job_part_prePressOp')->insertOrUpdate($prePressOp);
            }
            foreach ($part->getChangeOrders() as $changeOrder) {
                $forceUpdate |= $this->getCollectionAdapter('change_order')->insertOrUpdate($changeOrder);
            }
            foreach ($part->getProofs() as $proof) {
                $forceUpdate |= $this->getCollectionAdapter('proof')->insertOrUpdate($proof);
            }
            foreach ($part->getItems() as $item) {
                $forceUpdate |= $this->getCollectionAdapter('job_part_item')->insertOrUpdate($item);
            }
            foreach ($part->getPressForms() as $pressForm) {
                $forceUpdate |= $this->getCollectionAdapter('job_part_pressForm')->insertOrUpdate($pressForm);
            }
            foreach ($part->getComponents() as $component) {
                $forceUpdate |= $this->getCollectionAdapter('job_component')->insertOrUpdate($component);
            }
            foreach ($part->getFinishingOps() as $finishingOp) {
                $forceUpdate |= $this->getCollectionAdapter('job_part_finishingOp')->insertOrUpdate($finishingOp);
            }
            foreach ($part->getOutsidePurchs() as $outsidePurch) {
                $forceUpdate |= $this->getCollectionAdapter('job_part_outsidePurch')->insertOrUpdate($outsidePurch);
            }
            foreach ($part->getPlans() as $plan) {
                $forceUpdate |= $this->getCollectionAdapter('job_plan')->insertOrUpdate($plan);
            }
            foreach ($part->getCosts() as $cost) {
                $forceUpdate |= $this->getCollectionAdapter('job_cost')->insertOrUpdate($cost);
            }
            foreach ($part->getSizeAllowances() as $sizeAllowance) {
                $forceUpdate |= $this->getCollectionAdapter('job_part_sizeAllowance')->insertOrUpdate($sizeAllowance);
            }
        }
        foreach ($job->getJobContacts() as $jobContact) {
            $forceUpdate |= $this->getCollectionAdapter('job_contact')->insertOrUpdate($jobContact);
            $this->importContact($jobContact->getContact());
        }
        foreach ($job->getNotes() as $note) {
            $forceUpdate |= $this->getCollectionAdapter('job_note')->insertOrUpdate($note);
        }

        $this->tabs++;
        try {
            $i = 0;
            $count = count($job->getInvoices());
            foreach ($job->getInvoices() as $invoice) {
                $i++;
                $this->writeln("Invoice $i/$count: {$invoice->getId()}");
                try {
                    $this->tabs++;
                    $forceUpdate |= $this->importInvoice($invoice);
                } catch (\Exception $e) {
                    $this->writeln('Error: ' . $e->getMessage());
                } finally {
                    $this->tabs--;
                }
            }

            $i = 0;
            $count = count($job->getShipments());
            foreach ($job->getShipments() as $shipment) {
                $i++;
                $this->writeln("Shipment $i/$count: {$shipment->getId()}");
                try {
                    $this->tabs++;
                    $forceUpdate |= $this->importShipment($shipment);
                } catch (\Exception $e) {
                    $this->writeln('Error: ' . $e->getMessage());
                } finally {
                    $this->tabs--;
                }
            }
        } finally {
            $this->tabs--;
        }

        if (\Blackbox\Epace\Console\Command\EpaceMongoDebug::$debug) {
            if (!$job->getDateSetup() || !$job->getTimeSetUp()) {
                $this->writeln('[DEBUG] Job setup date or time is empty. ' . print_r($job->getData(), true));
            } else {
                $yearAgo = strtotime('-1 year');
                if (strtotime($job->getDateSetup()) + strtotime($job->getTimeSetUp()) < $yearAgo) {
                    $this->writeln('[DEBUG] Job setup datetime is earlier then a year. ' . print_r($job->getData(), true));
                }
            }
        }

        $this->getCollectionAdapter('job')->insertOrUpdate($job, $forceUpdate);

        $this->processedJobs[] = $job->getId();
    }

    protected function importShipment(\Blackbox\Epace\Model\Epace\Job\Shipment $jobShipment)
    {
        if (array_key_exists($jobShipment->getId(), $this->processedShipments)) {
            $this->writeln('JobShipment already processed');
            return $this->processedShipments[$jobShipment->getId()];
        }

        $forceUpdate = false;
        $forceUpdate |= $this->importContact($jobShipment->getContact());
        $forceUpdate |= $this->importContact($jobShipment->getShipTo());
//        $this->importShipVia($jobShipment->getShipVia());
        foreach ($jobShipment->getCartons() as $carton) {
            $forceUpdate |= $this->getCollectionAdapter('carton')->insertOrUpdate($carton);
            foreach ($carton->getContents() as $content) {
                $forceUpdate |= $this->getCollectionAdapter('carton_content')->insertOrUpdate($content);
            }
        }
        foreach ($jobShipment->getSkids() as $skid) {
            $forceUpdate |= $this->getCollectionAdapter('skid')->insertOrUpdate($skid);
        }

        return $this->processedShipments[$jobShipment->getId()] = $this->getCollectionAdapter('job_shipment')->insertOrUpdate($jobShipment, $forceUpdate);
    }

    protected function importInvoice(\Blackbox\Epace\Model\Epace\Invoice $invoice)
    {
        if (array_key_exists($invoice->getId(), $this->processedInvoices)) {
            $this->writeln('Invoice already processed');
            return $this->processedInvoices[$invoice->getId()];
        }

        $forceUpdate = false;
//        if ($invoice->getSalesCategory()) {
//            $this->getCollectionAdapter('salesCategory')->insertOrUpdate($invoice->getSalesCategory());
//        }
//        if ($invoice->getSalesTax()) {
//            $this->getCollectionAdapter('salesTax')->insertOrUpdate($invoice->getSalesTax());
//        }
        foreach ($invoice->getLines() as $line) {
            $forceUpdate |= $this->getCollectionAdapter('invoice_line')->insertOrUpdate($line);
        }
        foreach ($invoice->getTaxDists() as $taxDist) {
            $forceUpdate |= $this->getCollectionAdapter('invoice_taxDist')->insertOrUpdate($taxDist);
        }
        foreach ($invoice->getCommDists() as $commDist) {
            $forceUpdate |= $this->getCollectionAdapter('invoice_commDist')->insertOrUpdate($commDist);
        }
        foreach ($invoice->getExtras() as $extra) {
            $forceUpdate |= $this->getCollectionAdapter('invoice_extra')->insertOrUpdate($extra);
        }
        foreach ($invoice->getSalesDists() as $salesDist) {
            $forceUpdate |= $this->getCollectionAdapter('invoice_salesDist')->insertOrUpdate($salesDist);
        }

        if ($invoice->getReceivable()) {
            $forceUpdate |= $this->importReceivable($invoice->getReceivable());
        }

        return $this->processedInvoices[$invoice->getId()] = $this->getCollectionAdapter('invoice')->insertOrUpdate($invoice, $forceUpdate);
    }

    protected function importReceivable(\Blackbox\Epace\Model\Epace\Receivable $receivable)
    {
        if (array_key_exists($receivable->getId(), $this->processedReceivables)) {
            $this->writeln('Receivable already processed');
            return $this->processedReceivables[$receivable->getId()];
        }

        $forceUpdate = false;
        foreach ($receivable->getLines() as $line) {
            $forceUpdate |= $this->getCollectionAdapter('receivable_line')->insertOrUpdate($line);
        }

        return $this->processedReceivables[$receivable->getId()] = $this->getCollectionAdapter('receivable')->insertOrUpdate($receivable, $forceUpdate);
    }

    protected function importPurchaseOrder(\Blackbox\Epace\Model\Epace\Purchase\Order $purchaseOrder)
    {
        $forceUpdate = false;
        foreach ($purchaseOrder->getLines() as $line) {
            $forceUpdate |= $this->getCollectionAdapter('purchase_order_line')->insertOrUpdate($line);
        }
        if ($purchaseOrder->getVendor()) {
            $this->getCollectionAdapter('vendor')->insertOrUpdate($purchaseOrder->getVendor());
        }
        if ($purchaseOrder->getShipToContact()) {
            $this->getCollectionAdapter('contact')->insertOrUpdate($purchaseOrder->getShipToContact());
        }

        return $this->getCollectionAdapter('purchase_order')->insertOrUpdate($purchaseOrder, $forceUpdate);
    }

    protected function importCustomer($customer)
    {
        if ($customer instanceof \Blackbox\Epace\Model\Epace\Customer) {
            $this->getCollectionAdapter('customer')->insertOrUpdate($customer);
//            if ($customer->getSalesPerson()) {
//                $this->getCollectionAdapter('salesPerson')->insertOrUpdate($customer->getSalesPerson());
//            }
//            if ($customer->getSalesTax()) {
//                $this->getCollectionAdapter('salesTax')->insertOrUpdate($customer->getSalesTax());
//            }
//            if ($customer->getCSR()) {
//                $this->getCollectionAdapter('cSR')->insertOrUpdate($customer->getCSR());
//            }
//            if ($customer->getCountry()) {
//                $this->getCollectionAdapter('country')->insertOrUpdate($customer->getCountry());
//            }
//            if ($customer->getSalesCategory()) {
//                $this->getCollectionAdapter('salesCategory')->insertOrUpdate($customer->getSalesCategory());
//            }
        }
    }

    protected function importEntities($type)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var Blackbox_Epace_Model_Resource_Epace_Collection $collection */
        $collection = $objectManager->create('\Blackbox\Epace\Model\Resource\Epace\\'.$type.'\Collection');
        $adapter = $this->getCollectionAdapter($type);
        $this->writeln('Importing ' . $adapter->getCollectionName());
        $this->tabs++;
        try {
            foreach ($collection->getItems() as $item) {
                $this->writeln($item->getId());
                $adapter->insertOrUpdate($item);
            }
        } finally {
            $this->tabs--;
        }

        $adapter->flush();
    }

    protected function importContact($contact)
    {
        if ($contact instanceof \Blackbox\Epace\Model\Epace\Contact) {
            return $this->getCollectionAdapter('contact')->insertOrUpdate($contact);
        }
        return false;
    }

    protected function listNotImported(InputInterface $input, OutputInterface $output)
    {
        $entities = [
            'Estimate' => [
                'keys' => [
                    'e',
                    'estimates'
                ],
                'dateField' => 'entryDate',
            ],
            'Job' => [
                'keys' => [
                    'j',
                    'jobs'
                ],
                'dateField' => 'dateSetup',
            ],
            'Invoice' => [
                'keys' => [
                    'i',
                    'invoices'
                ],
                'dateField' => 'invoiceDate',
            ],
            'JobShipment' => [
                'keys' => [
                    's',
                    'shipments'
                ],
                'dateField' => 'date',
            ],
            'Receivable' => [
                'keys' => [
                    'r',
                    'receivables'
                ],
                'dateField' => 'invoiceDate'
            ],
            'PurchaseOrder' => [
                'keys' => [
                    'po',
                    'purchaseOrders'
                ],
                'dateField' => 'dateEntered'
            ],
        ];
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var Blackbox_Epace_Helper_Data $epaceHelper */
        $epaceHelper = $objectManager->create('\Blackbox\Epace\Helper\Epace');

        $dates = $input->getOption(self::DATES);

        foreach ($entities as $entity => $settings) {
            $found = false;
            foreach ($settings['keys'] as $key) {
                if ($input->getOption($key)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                continue;
            }

            $this->write($entity . ' ');

            $epaceModelType = $epaceHelper->getTypeName($entity);

            /** @var Blackbox_Epace_Model_Resource_Epace_Collection $epaceCollection */
            $epaceCollection = $objectManager->create('\Blackbox\Epace\Model\Resource\Epace\\'.$epaceModelType.'\Collection');

            if ( $from = $input->getOption(self::FROM) ) {
                $epaceCollection->addFilter($settings['dateField'], ['gteq' => new \DateTime($from)]);
            }
            if ( $to = $input->getOption(self::TO) ) {
                $epaceCollection->addFilter($settings['dateField'], ['lteq' => $this->timezone->date("2017-01-20T13:59:19+03:00")]);
            }

            $ids = $epaceCollection->loadIds();
            $this->writeln(count($ids));

            $filter = [];
            if ( !$input->getOption(self::NOMONGOFILTER) ) {
                if ($from) {
                    if (is_string($from) && !is_numeric($from)) {
                        $fromTimestamp = strtotime($from);
                    } else {
                        $fromTimestamp = $from;
                    }

                    $filter[$settings['dateField']] = ['$gte' => new MongoDB\BSON\UTCDateTime($fromTimestamp * 1000 - 3600000 * 24)];
                }
                if ($to) {
                    if (is_string($to) && !is_numeric($to)) {
                        $toTimestamp = strtotime($to);
                    } else {
                        $toTimestamp = $to;
                    }
                    $filter[$settings['dateField']] = ['$lte' => new MongoDB\BSON\UTCDateTime($toTimestamp * 1000 + 3600000 * 24)];
                }
            }

            $importedIds = $this->getCollectionAdapter($epaceModelType)->loadIds($filter);
            $count = 0;

            foreach ($ids as $id) {
                if (!in_array($id, $importedIds)) {
                    $count++;
                    if ($dates) {
                        $obj = $objectManager->create('\Blackbox\Epace\Model\Epace\\'.$epaceModelType)->load($id);
                        $this->writeln($id . "\t" . $obj->getData($settings['dateField']));
                    } else {
                        $this->writeln($id);
                    }
                }
            }

            $this->writeln($entity . 's missed:' . $count);
        }
    }

    protected function getDeleteDependencies()
    {
        return [
            'Estimate' => [
                'EstimateProduct' => [
                    'EstimatePart' => [
                        'EstimateQuantity',
                        'EstimatePartSizeAllowance'
                    ],
                    'EstimateProductPriceSummary'
                ],
                'EstimateQuoteLetter' => [
                    'EstimateQuoteLetterNote'
                ]
            ],
            'Job' => [
                'JobProduct',
                'JobContact',
                'JobShipment' => [
                    'Carton' => [
                        'CartonContent'
                    ],
                    'Skid',
                ],
                'JobNote',
                'JobPart' => [
                    'JobMaterial',
                    'JobPartPrePressOp',
                    'ChangeOrder',
                    'Proof',
                    'JobPartItem',
                    'JobPartPressForm',
                    'JobComponent',
                    'JobPartFinishingOp',
                    'JobPartOutsidePurch',
                    'JobPlan',
                    'JobCost',
                    'JobPartSizeAllowance'
                ],
                'Invoice' => [
                    'InvoiceCommDist',
                    'InvoiceExtra',
                    'InvoiceLine',
                    'InvoiceSalesDist',
                    'InvoiceTaxDist'
                ]
            ],
            'PurchaseOrder' => [
                'PurchaseOrderLine'
            ],
            'Receivable' => [
                'ReceivableLine'
            ],
            'Skid',
        ];
    }

    protected function printDeletedEntities(InputInterface $input, OutputInterface $output)
    {
        $entity = '';
        if (!$entity) {
            $entity = $input->getOption(self::PRINTDELETEDENTITIES);
        }

        if ($entity) {
            $this->writeln(implode(PHP_EOL, $this->getDeleted($entity)));
        } else {
            $dependencies = $this->getDeleteDependencies();
            foreach ($dependencies as $key => $value) {
                if (is_array($value)) {
                    $this->printDeletedEntitiesRecursive($key, $value);
                } else {
                    $this->printDeletedEntitiesRecursive($value);
                }
            }
        }
    }

    protected function deleteEntities()
    {
        $dependencies = $this->getDeleteDependencies();

        $relations = [];

        $this->buildEntitiesRelations($relations, null, $dependencies);

        foreach ($dependencies as $key => $value) {
            if (is_array($value)) {
                $this->deleteEntitiesRecursive($relations, $key);
            } else {
                $this->deleteEntitiesRecursive($relations, $value);
            }
        }
    }

    protected function deleteEntitiesRecursive(array &$relations, $entity)
    {
        $this->writeln('Process deleted entities: ' . $entity);

        $ids = $this->getDeleted($entity);
        $count = count($ids);
        $this->writeln('Found ' . $count . ' deleted');
        $i = 0;

        $parents = $relations[$entity]['parents'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        /** @var Blackbox_Epace_Helper_Data $helper */
        $helper = $objectManager->create('\Blackbox\Epace\Helper\Epace');
        $typeName = $helper->getTypeName($entity);

        foreach ($ids as $id) {
            $this->writeln(++$i . '/' . $count . ' ' . $id);

            if (!empty($parents)) {
                try {
                    \Blackbox\Epace\Model\Epace\EpaceObject::$useMongo = true;

                    $mongoObject = $objectManager->create('\Blackbox\Epace\Model\Epace\\'.$typeName)->load($id);

                    foreach ($parents as $parent) {
                        $method = 'get' . $parent;
                        if (!method_exists($mongoObject, $method)) {
                            throw new \Exception('Method "' . $method . '" don\'t exists in ' . get_class($mongoObject));
                        }

                        /** @var Blackbox_Epace_Model_Epace_AbstractObject $parentMongoObject */
                        $parentMongoObject = $mongoObject->$method();
                        if ($parentMongoObject) {
                            $this->tabs++;
                            try {
                                $this->writeln('Update parent entity ' . $parent . ' ' . $parentMongoObject->getId());
                                $this->updateParentsRecursive($relations, $parentMongoObject);
                            } finally {
                                $this->tabs--;
                            }
                        }
                    }
                } finally {
                    \Blackbox\Epace\Model\Epace\EpaceObject::$useMongo = false;
                }
            }

            $this->getCollectionAdapter($typeName)->deleteId($id)->flush();
        }

        if (isset($relations[$entity]['children'])) {
            foreach ($relations[$entity]['children'] as $childEntity) {
                $this->deleteEntitiesRecursive($relations, $childEntity);
            }
        }
    }

    protected function updateParentsRecursive(&$relations, \Blackbox\Epace\Model\Epace\EpaceObject $mongoObject)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        /** @var Blackbox_Epace_Helper_Data $helper */
        $helper = $objectManager->create('\Blackbox\Epace\Helper\Epace');
        $typeName = $helper->getTypeName($mongoObject->getObjectType());

        $this->getCollectionAdapter($typeName)->updateDataRawById(['_updated_at' => new MongoDB\BSON\UTCDateTime(time() * 1000)], $mongoObject->getId());

        $parents = $relations[$mongoObject->getObjectType()]['parents'];

        $useMongoPrevious = \Blackbox\Epace\Model\Epace\EpaceObject::$useMongo;
        try {
            \Blackbox\Epace\Model\Epace\EpaceObject::$useMongo = true;

            foreach ($parents as $parent) {
                $method = 'get' . $parent;
                if (!method_exists($mongoObject, $method)) {
                    throw new \Exception('Method "' . $method . '" don\'t exists in ' . get_class($mongoObject));
                }

                $parentMongoObject = $mongoObject->$method();
                if ($parentMongoObject) {
                    $this->writeln('Update parent entity ' . $parent . ' ' . $parentMongoObject->getId());
                    $this->updateParentsRecursive($relations, $parentMongoObject);
                }
            }
        } finally {
            \Blackbox\Epace\Model\Epace\EpaceObject::$useMongo = $useMongoPrevious;
        }
    }

    protected function buildEntitiesRelations(array &$relations, $parent, $children)
    {
        if (!empty($parent)) {
            if (!isset($relations[$parent])) {
                $relations[$parent] = [
                    'parents' => [],
                    'children' => []
                ];
            }
        }

        if (!empty($children)) {
            foreach ($children as $key => $value) {
                if (is_array($value)) {
                    $child = $key;
                    $childChildren = $value;
                } else {
                    $child = $value;
                    $childChildren = null;
                }

                $this->buildEntitiesRelations($relations, $child, $childChildren);
                $relations[$parent]['children'][] = $child;
                if (!empty($parent)) {
                    $relations[$child]['parents'][] = $parent;
                }
            }
        }
    }

    protected function printDeletedEntitiesRecursive($entity, $children = null)
    {
        $this->writeln($entity);

        $deleted = $this->getDeleted($entity);
        $this->writeln(implode(PHP_EOL, $deleted));

        if (!empty($children)) {
            foreach ($children as $key => $value) {
                if (is_array($value)) {
                    $this->printDeletedEntitiesRecursive($key, $value);
                } else {
                    $this->printDeletedEntitiesRecursive($value);
                }
            }
        }
    }

    protected function getDeleted($entity)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('\Blackbox\Epace\Helper\Epace');

        $class = $helper->getTypeName($entity);
        $importedIds = $this->getCollectionAdapter($class)->loadIds();

        $useMongoPrevious = \Blackbox\Epace\Model\Epace\EpaceObject::$useMongo;
        \Blackbox\Epace\Model\Epace\EpaceObject::$useMongo = false;

        try {
            /** @var Blackbox_Epace_Model_Resource_Epace_Collection $collection */
            $collection = $objectManager->create('\Blackbox\Epace\Model\Resource\Epace\\'.$class.'\Collection');
            $actualIds = $this->loadEpaceIdsByPages($collection);
        } finally {
            \Blackbox\Epace\Model\Epace\EpaceObject::$useMongo = $useMongoPrevious;
        }

        return array_diff($importedIds, $actualIds);
    }

    protected function loadEpaceIdsByPages(\Blackbox\Epace\Model\Resource\Epace\Collection $collection)
    {
        $pageSize = 250000;
        $collection->setPageSize($pageSize);
        $page = 0;

        $result = [];
        do {
            $collection->clear()->setCurPage(++$page);
            $current = $collection->loadIds();
            if (!empty($current)) {
                $result = array_merge($result, $current);
            }
        } while (count($current) >= $pageSize);

        return $result;
    }

    /**
     * @param $class
     * @return MongoEpaceCollection
     */
    protected function getCollectionAdapter($class)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!isset($this->collectionAdapters[$class]) || !$this->collectionAdapters[$class]) {
            $this->collectionAdapters[$class] = new MongoEpaceCollection($objectManager->create('\Blackbox\Epace\Model\Epace\\'.$class), $this->manager, $this->database);
        } 

        return $this->collectionAdapters[$class];
    }

    protected function write($message)
    {
        echo str_repeat("\t", $this->tabs) . $message;
    }

    protected function writeln($message)
    {
        echo str_repeat("\t", $this->tabs) . $message . PHP_EOL;
    }
}
