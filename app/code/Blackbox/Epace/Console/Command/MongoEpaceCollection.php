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
/**
 * Class EpaceMongo
 */
class MongoEpaceCollection extends Command
{
    protected $epaceResource;
    /**
     * @var MongoDB\Driver\Manager
     */
    protected $manager;
    protected $database;

    /**
     * @var Blackbox_Epace_Helper_Mongo
     */
    protected $api;

    /**
     * @var MongoDB\Driver\BulkWrite
     */
    protected $bulkWrite;
    protected $currentBulkWriteIds = [];

    public static $bulkWriteLimit = 500;

    protected $existingIds = [];

    public function __construct($epaceResource, $manager, $database)
    {
        $this->epaceResource = $epaceResource;
        $this->manager = $manager;
        $this->database = $database;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('epace:mongo')->setDescription('Test console command');
    }
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Hello world!");
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->epaceResource->getObjectType();
    }

    /**
     * @return string
     */
    public function getPrimariKey()
    {
        return $this->epaceResource->getIdFieldName();
    }

    /**
     * @return Blackbox_Epace_Model_Epace_AbstractObject
     */
    public function getResource()
    {
        return $this->epaceResource;
    }

    public function exists(\Blackbox\Epace\Model\Epace\EpaceObject $object)
    {
        $this->validateObject($object);

        return $this->isIdExists($object->getId());
    }

    protected function isIdExists($id)
    {
        if (in_array($id, $this->existingIds)) {
            return true;
        }

        //$id = new \MongoDB\BSON\ObjectID($id);
        $filter = ['_id' => $id];
        $options = [
            'projection' => ['_id' => 1]
        ];

        $query = new \MongoDB\Driver\Query($filter, $options);
        $rows = $this->manager->executeQuery($this->database . '.' . $this->getCollectionName(), $query);

        $result = count($rows->toArray()) > 0;
        if ($result) {
            $this->existingIds[] = $id;
        }

        return $result;
    }

    public function loadData($id)
    {
        $query = new MongoDB\Driver\Query([
            '_id' => $id
        ]);
        $rows = $this->manager->executeQuery($this->database . '.' . $this->getCollectionName(), $query);
        foreach ($rows as $row) {
            return (array)$row;
        }
        return null;
    }

    public function loadIds($filter = [])
    {
        $options = [
            'projection' => ['_id' => 1]
        ];
        $rows = $this->manager->executeQuery($this->database . '.' . $this->getCollectionName(), new MongoDB\Driver\Query($filter, $options))->toArray();

        $result = [];
        foreach ($rows as $row) {
            $result[] = $row->_id;
        }

        return $result;
    }

    public function deleteIds(array $ids)
    {
        foreach ($ids as $id) {
            $this->deleteId($id);
        }

        $this->flush();

        return $this;
    }

    public function deleteId($id)
    {
        if (!$this->bulkWrite) {
            $this->bulkWrite = new MongoDB\Driver\BulkWrite(['ordered' => true]);
        }

        $this->bulkWrite->delete(['_id' => $this->_prepareIdValue($id)]);

        if ($this->bulkWrite->count() >= self::$bulkWriteLimit) {
            $this->flush();
        }

        return $this;
    }

    protected function _prepareIdValue($value)
    {
        if (is_null($value)) {
            return $value;
        }
        $type = $this->epaceResource->getDefinition()[$this->epaceResource->getIdFieldName()];
        switch ($type) {
            case 'int':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'date':
                if ($value instanceof \DateTime) {
                    $timestamp = $value->getTimestamp();
                } else if (is_numeric($value)) {
                    $timestamp = $value;
                } else {
                    $date = new \DateTime($value, new \DateTimeZone('Z'));
                    $timestamp = $date->getTimestamp();
                }
                return new \MongoDB\BSON\UTCDateTime($timestamp * 1000);
            default:
                return (string)$value;
        }
    }

    public function insertOrUpdate(\Blackbox\Epace\Model\Epace\EpaceObject $object, $forceUpdate = false)
    {
        $this->validateObject($object);
        return $this->insertOrUpdateData($object->getData(), $forceUpdate);
    }

    protected function insertOrUpdateData($data, $forceUpdate = false)
    {
        $id = $data[$this->epaceResource->getIdFieldName()];
        if (in_array($id, $this->currentBulkWriteIds)) {
            if ($forceUpdate) {
                $this->flush();
            } else {
                return true;
            }
        }

        if (!$this->bulkWrite) {
            $this->bulkWrite = new MongoDB\Driver\BulkWrite(['ordered' => true]);
        }

        $data = $this->_prepareData($data, true);
        if ($old = $this->loadData($id)) {
            $systemFields = [
                '_created_at',
                '_updated_at',
                '_id'
            ];
            foreach ($systemFields as $field) {
                unset($old[$field]);
            }

            $update = false;
            foreach ($data as $key => $value) {
                if (in_array($key, $systemFields)) {
                    continue;
                }
                if ($value != $old[$key]) {
                    $update = true;
                    break;
                } else {
                    unset($old[$key]);
                }
            }
            if (!$update && empty($old)) {
                if ($forceUpdate) {
                    if (EpaceMongoDebug::$debug) {
                        echo $this->getCollectionName() . ' FORCE UPDATE' . PHP_EOL;
                    }
                    $this->bulkWrite->update(
                        ['_id' => $id],
                        ['$set' => ['_updated_at' => new MongoDB\BSON\UTCDateTime(time() * 1000)]],
                        ['multi' => false]);
                } else {
                    if (EpaceMongoDebug::$debug) {
                        echo $this->getCollectionName() . ' IGNORE' . PHP_EOL;
                    }
                    return false;
                }
            }
            $data['_updated_at'] = new MongoDB\BSON\UTCDateTime(time() * 1000);
            $this->bulkWrite->update(
                ['_id' => $id],
                ['$set' => $data],
                ['multi' => false]
            );
            if (EpaceMongoDebug::$debug) {
                echo $this->getCollectionName() . ' UPDATE' . PHP_EOL;
            }
        } else {
            $data['_created_at'] = new MongoDB\BSON\UTCDateTime(time() * 1000);
            $data['_updated_at'] = new MongoDB\BSON\UTCDateTime(time() * 1000);
            $this->bulkWrite->insert($data);
            if (EpaceMongoDebug::$debug) {
                echo $this->getCollectionName() . ' INSERT' . PHP_EOL;
            }
        }
        $this->currentBulkWriteIds[] = $id;

        if ($this->bulkWrite->count() >= self::$bulkWriteLimit) {
            $this->flush();
        }

        return true;
    }

    public function updateDataRaw($data)
    {
        $id = $data[$this->epaceResource->getIdFieldName()];
        unset($data[$this->epaceResource->getIdFieldName()]);

        $this->updateDataRawById($data, $id);
    }

    public function updateDataRawById($data, $id)
    {
        $id = $this->_prepareIdValue($id);

        if (in_array($id, $this->currentBulkWriteIds)) {
            return;
        }

        if (!$this->bulkWrite) {
            $this->bulkWrite = new MongoDB\Driver\BulkWrite(['ordered' => true]);
        }

        $this->bulkWrite->update(
            ['_id' => $id],
            ['$set' => $data],
            ['multi' => false]
        );
        $this->currentBulkWriteIds[] = $id;

        if ($this->bulkWrite->count() >= self::$bulkWriteLimit) {
            $this->flush();
        }
    }

    public function checkCollection()
    {
        $response = $this->manager->executeCommand($this->database, new \MongoDB\Driver\Command(['listCollections' => 1]))->toArray();
        foreach ($response as $collection) {
            if ($collection->name == $this->getCollectionName()) {
                return;
            }
        }
        $response = $this->manager->executeCommand($this->database, new \MongoDB\Driver\Command(['create' => $this->getCollectionName()]));
    }

    public function flush()
    {
        if ($this->bulkWrite && $this->bulkWrite->count() > 0) {
            $this->manager->executeBulkWrite($this->database . '.' . $this->getCollectionName(), $this->bulkWrite);
            $this->bulkWrite = null;

            foreach ($this->currentBulkWriteIds as $id) {
                $this->existingIds[] = $id;
            }

            $this->currentBulkWriteIds = [];
        }
    }

    public function __destruct()
    {
        $this->flush();
    }

    protected function validateObject(\Blackbox\Epace\Model\Epace\EpaceObject $object)
    {
        if ($this->epaceResource->getObjectType() != $object->getObjectType()) {
            throw new \Exception('Trying add to collection of type ' . $this->epaceResource->getObjectType() . ' object of type ' . $object->getObjectType());
        }
    }

    /**
     * @param array $data
     * @param bool $addId
     * @return array
     */
    protected function _prepareData($data, $addId)
    {
        foreach ($this->epaceResource->getDefinition() as $field => $type) {
            if (!array_key_exists($field, $data) || is_null($data[$field])) {
                continue;
            }
            if ($type == 'date') {
                if (is_string($data[$field]) && !is_numeric($data[$field])) {
                    $data[$field] = new MongoDB\BSON\UTCDateTime(strtotime($data[$field]) * 1000);
                } else {
                    $data[$field] = new MongoDB\BSON\UTCDateTime((int)$data[$field] * 1000);
                }
            }
        }
        if ($addId) {
            $data['_id'] = $data[$this->epaceResource->getIdFieldName()];
        }
        return $data;
    }
}
