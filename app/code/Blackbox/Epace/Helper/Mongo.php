<?php

namespace Blackbox\Epace\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Mongo extends \Magento\Framework\App\Helper\AbstractHelper {

    const JOB_STATUS_OPEN = 'O';
    const JOB_STATUS_CLOSED = 'C';

    protected $event;
    protected $host;
    protected $database;
    protected $manager;

    public function __construct(
            \Magento\Framework\App\Helper\Context $context, 
            \Magento\Store\Model\StoreManagerInterface $storeManager, 
            \Magento\Cms\Model\Template\FilterProvider $filterProvider, 
            \Blackbox\Soap\Model\Api $api
    ) {
        parent::__construct($context);
        $this->_filterProvider = $filterProvider;
        $this->_storeManager = $storeManager;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('\Blackbox\Soap\Model\Api');
        $this->api = $model;
        $this->host = $this->scopeConfig->getValue('epace/mongo/host', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null);
        $this->database = $this->scopeConfig->getValue('epace/mongo/database', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null);
        $this->manager = new MongoDB\Driver\Manager($this->host);
    }

    public function setAuthInfo($username, $password) {
        
    }

    public function setCompany($company) {
        
    }

    public function setEvent(\Blackbox\Epace\Model\Event $event) {
        $this->event = $event;
    }

    /**
     * @return Blackbox_Epace_Model_Event|null
     */
    public function getEvent() {
        return $this->event;
    }

    public function getHost() {
        return $this->host;
    }

    public function getDatabase() {
        return $this->database;
    }

    public function readEstimate($id) {
        return $this->readObject('estimate', [
                    'id' => $id
        ]);
    }

    public function readObject($objectType, $params) {
        $data = $this->_loadData($objectType, $params);
        foreach ($data as &$value) {
            if ($value instanceof \MongoDB\BSON\UTCDateTime) {
                $value = $value->toDateTime()->format('Y-m-d\TH:i:s.0000\Z');
            }
        }

        return $data;
    }

    protected function _loadData($objectType, $params) {
        $objectType = ucfirst($objectType);

        $query = new MongoDB\Driver\Query($params);
        $rows = $this->manager->executeQuery($this->database . '.' . $objectType, $query);
        foreach ($rows as $row) {
            return (array) $row;
        }

        $this->throwException('Object ' . $objectType . ' not found.', null);
    }

    public function findObjects($objectType, $filter, $sort = null, $offset = null, $limit = null) {
        $objectType = ucfirst($objectType);

        if (!is_array($filter)) {
            $filter = (array) $filter;
        }

        $options = [
            'projection' => ['_id' => 1]
        ];


        $query = new MongoDB\Driver\Query($filter, $options);

        $rows = $this->manager->executeQuery($this->database . '.' . $objectType, $query);

        $result = [];
        foreach ($rows as $row) {
            $result[] = $row->_id;
        }

        return $result;
    }

    public function createEmployeeTime($employee, $startDate, $startTime, $stopDate = null, $stopTime = null, array $otherSettings = array()) {
        $settings = array_merge(array(
            'employee' => $employee,
            'startDate' => (new DateTime($startDate))->format('Y-m-d\TH:i:s.0000\Z'),
            'startTime' => (new DateTime($startTime))->format('1970-01-01\TH:i:s.0000\Z'),
            'stopDate' => $stopDate ? (new DateTime($stopDate))->format('Y-m-d\TH:i:s.0000\Z') : null,
            'stopTime' => $stopTime ? (new DateTime($stopTime))->format('1970-01-01\TH:i:s.0000\Z') : null
                ), $otherSettings);

        return $this->createObject($settings, 'employeeTime');
    }

    public function createJob($customer, $description, array $otherSettings = array()) {
        $settings = array_merge(array('customer' => $customer, 'description' => $description), $otherSettings);

        return $this->createObject($settings, 'job');
    }

    public function updateJob($job, $status, array $otherSettings = array()) {
        $settings = array_merge(array('job' => $job, 'adminStatus' => $status), $otherSettings);

        return $this->updateObject($settings, 'job');
    }

    public function createJobProduct($jobId, $description, $qtyOrdered = 1, $salesCategory = 1, $otherSettings) {
        $settings = array_merge(array(
            'job' => $jobId,
            'description' => $description,
            'qtyOrdered' => $qtyOrdered,
            'salesCategory' => $salesCategory
                ), $otherSettings);

        return $this->createObject($settings, 'jobProduct');
    }

    public function logCallback($type, $content, $action, $url = null, $headers = null, $requestFileId = null) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fileModel = $objectManager->create('\Blackbox\Epace\Model\Event\File');
        $file = $fileModel->setData(array(
                    'event_id' => $this->event->getId(),
                    'type' => $type,
                    'action' => $url,
                    'content' => $content,
                    'ext' => 'xml',
                    'related_file_id' => $requestFileId
                ))->save();
        
        if ($type == \Blackbox\Soap\Model\Api::LOG_TYPE_REQUEST) {
            return $file->getId();
        }
    }

    public function createObject($settings, $objectType) {
        
    }

    public function updateObject($settings, $objectType) {
        
    }

    public function renderFilters($filters, \Blackbox\Epace\Model\Epace\EpaceObject $resource) {
        $result = [];
        if (count($filters) == 1) {
            $result = $this->_renderFilter($filters[0], $resource);
        } else {
            $and = false;
            $renderedFilters = [];
            $fields = [];
            foreach ($filters as $filter) {
                if ($filter['type'] != 'and') {
                    if ($filter['type'] == 'or') {
                        throw new \Exception('OR filter not supported.');
                    }
                    throw new \Exception('Unrecognized filter type.');
                }
                $renderedFilter = $this->_renderFilter($filter, $resource);
                if (!$and) {
                    $field = array_keys($renderedFilter)[0];
                    if (in_array($field, $fields)) {
                        $and = true;
                    } else {
                        $fields[] = $field;
                    }
                }
                $renderedFilters[] = $renderedFilter;
            }

            if ($and) {
                foreach ($renderedFilters as $renderedFilter) {
                    $result['$and'][] = $renderedFilter;
                }
            } else {
                foreach ($renderedFilters as $renderedFilter) {
                    $result = array_merge($result, $renderedFilter);
                }
            }
        }

        return $result;
    }

    public function renderOrders($orders) {
        foreach ($orders as &$direction) {
            switch ($direction) {
                case 'DESC':
                    $direction = -1;
                    break;
                case 'ASC':
                    $direction = 1;
                    break;
                default:
                    throw new \Exception('Invalid sort direction: ' . $direction);
            }
        }

        return $orders;
    }

    protected function _renderFilter($filter, \Blackbox\Epace\Model\Epace\EpaceObject $resource) {
        $definition = $resource->getDefinition();
        $definition['_created_at'] = 'date';
        $definition['_updated_at'] = 'date';
        $definition['_id'] = $definition[$resource->getIdFieldName()];

        if (is_array($filter['value'])) {
            $conditionKeyMap = [
                'eq' => '$eq',
                'neq' => '$ne',
                'gt' => '$gt',
                'lt' => '$lt',
                'gteq' => '$gte',
                'lteq' => '$lte',
            ];

            $regexConditionKeyMap = [
                'starts' => '^{{value}}',
                'ends' => '{{value}}$',
                'contains' => '{{value}}'
            ];

            foreach ($filter['value'] as $k => $v) {
                if ($conditionKeyMap[$k]) {
                    return [
                        $filter['field'] => [$conditionKeyMap[$k] => $this->_renderFilterValue($v, $definition[$filter['field']])]
                    ];
                }

                if ($regexConditionKeyMap[$k]) {
                    $regex = new MongoDB\BSON\Regex(str_replace('{{value}}', preg_quote($v, '/'), $regexConditionKeyMap[$k]));
                    return [
                        $filter['field'] => $regex
                    ];
                }

                break;
            }

            throw new \Exception('Unable to render filters.');
        } else {
            return [$filter['field'] => $this->_renderFilterValue($filter['value'], $definition[$filter['field']])];
        }
    }

    protected function _renderFilterValue($value, $type = null) {
        if ($value instanceof \DateTime) {
            return new MongoDB\BSON\UTCDateTime($value->getTimestamp() * 1000);
        }
        if ($type == 'date') {
            if (is_string($value) && !is_numeric($value)) {
                $value = strtotime($value);
            }
            return new MongoDB\BSON\UTCDateTime($value * 1000);
        } if ($type == 'string') {
            return (string) $value;
        } else if ($type == 'int') {
            return (int) $value;
        } else {
            return $value;
        }
    }

    protected function throwException($message, $response) {
        $e = new Blackbox_Epace_Model_Exception($message);
        $e->setResponse($response);
        throw $e;
    }

}
