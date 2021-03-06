<?php
namespace Blackbox\EpaceImport\Model\Resource\PurchaseOrder;

abstract class PurchaseOrderAbstract extends \Magento\Sales\Model\ResourceModel\EntityAbstract
{
    /**
     * Is grid available
     *
     * @var boolean
     */
    protected $_grid                         = false;

    /**
     * Use additional is object new check for this resource
     *
     * @var boolean
     */
    protected $_useIsObjectNew               = true;

    /**
     * Flag for using of increment id
     *
     * @var boolean
     */
    protected $_useIncrementId               = false;

    /**
     * Entity code for increment id (Eav entity code)
     *
     * @var string
     */
    protected $_entityTypeForIncrementId     = '';

    /**
     * Grid virtual columns
     *
     * @var array|null
     */
    protected $_virtualGridColumns           = null;

    /**
     * Grid columns
     *
     * @var array|null
     */
    protected $_gridColumns                  = null;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix                  = 'epacei_resource';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject                  = 'resource';

    /**
     * Add new virtual grid column
     *
     * @param string $alias
     * @param string $table
     * @param array $joinCondition
     * @param string $column
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\PurchaseOrderAbstract
     */
    public function addVirtualGridColumn($alias, $table, $joinCondition, $column)
    {
        $table = $this->getTable($table);

        if (!in_array($alias, $this->getGridColumns())) {
            $errorMsg ='Please specify a valid grid column alias name that exists in grid table.';
            throw new \Exception($errorMsg);
        }

        $this->_virtualGridColumns[$alias] = array(
            $table, $joinCondition, $column
        );

        return $this;
    }

    /**
     * Retrieve virtual grid columns
     *
     * @return array
     */
    public function getVirtualGridColumns()
    {
        if ($this->_virtualGridColumns === null) {
            $this->_initVirtualGridColumns();
        }

        return $this->_virtualGridColumns;
    }

    /**
     * Init virtual grid records for entity
     *
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\PurchaseOrderAbstract
     */
    protected function _initVirtualGridColumns()
    {
        $this->_virtualGridColumns = array();
        if ($this->_eventPrefix && $this->_eventObject) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();	
            /** @var \Magento\Framework\Event\ManagerInterface $manager */
            $manager = $om->get('Magento\Framework\Event\ManagerInterface');
            $manager->dispatch($this->_eventPrefix . '_init_virtual_grid_columns', array(
                    $this->_eventObject => $this
                ));
        }
        return $this;
    }

    /**
     * Update records in grid table
     *
     * @param array|int $ids
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\PurchaseOrderAbstract
     */
    public function updateGridRecords($ids)
    {
        if ($this->_grid) {
            if (!is_array($ids)) {
                $ids = array($ids);
            }

            if ($this->_eventPrefix && $this->_eventObject) {
                $proxy = new \Magento\Framework\DataObjectFactory();
                $proxy->setIds($ids)
                    ->setData($this->_eventObject, $this);

                $om = \Magento\Framework\App\ObjectManager::getInstance();	
                /** @var \Magento\Framework\Event\ManagerInterface $manager */
                $manager = $om->get('\Magento\Framework\Event\ManagerInterface');
                $manager->dispatch($this->_eventPrefix . '_update_grid_records', array('proxy' => $proxy));
                $ids = $proxy->getIds();
            }

            if (empty($ids)) { // If nothing to update
                return $this;
            }
            $columnsToSelect = array();
            $table = $this->getGridTable();
            $select = $this->getUpdateGridRecordsSelect($ids, $columnsToSelect);
            $this->_getWriteAdapter()->query($select->insertFromSelect($table, $columnsToSelect, true));
        }

        return $this;
    }

    /**
     * Retrieve update grid records select
     *
     * @param array $ids
     * @param array $flatColumnsToSelect
     * @param array|null $gridColumns
     * @return Varien_Db_Select
     */
    public function getUpdateGridRecordsSelect($ids, &$flatColumnsToSelect, $gridColumns = null)
    {
        $flatColumns = array_keys($this->_getReadAdapter()
            ->describeTable(
                $this->getMainTable()
            )
        );

        if ($gridColumns === null) {
            $gridColumns = $this->getGridColumns();
        }

        $flatColumnsToSelect = array_intersect($flatColumns, $gridColumns);

        $select = $this->_getWriteAdapter()->select()
                ->from(array('main_table' => $this->getMainTable()), $flatColumnsToSelect)
                ->where('main_table.' . $this->getIdFieldName() . ' IN(?)', $ids);

        $this->joinVirtualGridColumnsToSelect('main_table', $select, $flatColumnsToSelect);

        return $select;
    }

    /**
     * Join virtual grid columns to select
     *
     * @param string $mainTableAlias
     * @param Zend_Db_Select $select
     * @param array $columnsToSelect
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\PurchaseOrderAbstract
     */
    public function joinVirtualGridColumnsToSelect($mainTableAlias, Zend_Db_Select $select, &$columnsToSelect)
    {
        $adapter = $this->_getWriteAdapter();
        foreach ($this->getVirtualGridColumns() as $alias => $expression) {
            list($table, $joinCondition, $column) = $expression;
            $tableAlias = 'table_' . $alias;

            $joinConditionExpr = array();
            foreach ($joinCondition as $fkField=>$pkField) {
                $pkField = $adapter->quoteIdentifier(
                    $tableAlias . '.' . $pkField
                );
                $fkField = $adapter->quoteIdentifier(
                    $mainTableAlias . '.' . $fkField
                );
                $joinConditionExpr[] = $fkField . '=' . $pkField;
            }

            $select->joinLeft(
                array($tableAlias=> $table),
                implode(' AND ', $joinConditionExpr),
                array($alias => str_replace('{{table}}', $tableAlias, $column))
            );

            $columnsToSelect[] = $alias;
        }

        return $this;
    }

    /**
     * Retrieve list of grid columns
     *
     * @return array
     */
    public function getGridColumns()
    {
        if ($this->_gridColumns === null) {
            if ($this->_grid) {
                $this->_gridColumns = array_keys(
                    $this->_getReadAdapter()->describeTable($this->getGridTable())
                );
            } else {
                $this->_gridColumns = array();
            }
        }

        return $this->_gridColumns;
    }

    /**
     * Retrieve grid table
     *
     * @return string
     */
    public function getGridTable()
    {
        if ($this->_grid) {
            return $this->getTable($this->_mainTable . '_grid');
        }
        return false;
    }

    /**
     * Before save object attribute
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $attribute
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\PurchaseOrderAbstract
     */
    protected function _beforeSaveAttribute(\Magento\Framework\Model\AbstractModel $object, $attribute)
    {
        if ($this->_eventObject && $this->_eventPrefix) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();	
            /** @var \Magento\Framework\Event\ManagerInterface $manager */
            $manager = $om->get('Magento\Framework\Event\ManagerInterface');
            $manager->dispatch($this->_eventPrefix . '_save_attribute_before', array(
                $this->_eventObject => $this,
                'object' => $object,
                'attribute' => $attribute
            ));
        }
        return $this;
    }

    /**
     * After save object attribute
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $attribute
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\PurchaseOrderAbstract
     */
    protected function _afterSaveAttribute(\Magento\Framework\Model\AbstractModel $object, $attribute)
    {
        if ($this->_eventObject && $this->_eventPrefix) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();	
            /** @var \Magento\Framework\Event\ManagerInterface $manager */
            $manager = $om->get('Magento\Framework\Event\ManagerInterface');
            $manager->dispatch($this->_eventPrefix . '_save_attribute_after', array(
                $this->_eventObject => $this,
                'object' => $object,
                'attribute' => $attribute
            ));
        }
        return $this;
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $attribute
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\PurchaseOrderAbstract
     */
    public function saveAttribute(\Magento\Framework\Model\AbstractModel $object, $attribute)
    {
        if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute) {
            $attribute = $attribute->getAttributeCode();
        }

        if (is_string($attribute)) {
            $attribute = array($attribute);
        }

        if (is_array($attribute) && !empty($attribute)) {
            $this->beginTransaction();
            try {
                $this->_beforeSaveAttribute($object, $attribute);
                $data = new \Magento\Framework\DataObjectFactory();
                foreach ($attribute as $code) {
                    $data->setData($code, $object->getData($code));
                }

                $updateArray = $this->_prepareDataForTable($data, $this->getMainTable());
                $this->_postSaveFieldsUpdate($object, $updateArray);
                if (!$object->getForceUpdateGridRecords() &&
                    count(array_intersect($this->getGridColumns(), $attribute)) > 0
                ) {
                    $this->updateGridRecords($object->getId());
                }
                $this->_afterSaveAttribute($object, $attribute);
                $this->commit();
            } catch (Exception $e) {
                $this->rollBack();
                throw $e;
            }
        }

        return $this;
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\DataObjectFactory $object
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\PurchaseOrderAbstract
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->_useIncrementId && !$object->getIncrementId()) {
            /* @var $entityType \Mage\Eav\Model\Entity\Type */
            $om = \Magento\Framework\App\ObjectManager::getInstance();	
            /** @var \Magento\Framework\Event\ManagerInterface $manager */
            $manager = $om->get('Magento\Eav\Entity\Type');
            $entityType = $manager->loadByCode($this->_entityTypeForIncrementId);
            $object->setIncrementId($entityType->fetchNewIncrementId($object->getStoreId()));
        }
        parent::_beforeSave($object);
        return $this;
    }

    /**
     * Update field in table if model have been already saved
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param array $data
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\PurchaseOrderAbstract
     */
    protected function _postSaveFieldsUpdate($object, $data)
    {
        if ($object->getId() && !empty($data)) {
            $table = $this->getMainTable();
            $this->_getWriteAdapter()->update($table, $data,
                array($this->getIdFieldName() . '=?' => (int) $object->getId())
            );
            $object->addData($data);
        }

        return $this;
    }

    /**
     * Set main resource table
     *
     * @param string $table
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\PurchaseOrderAbstract
     */
    public function setMainTable($table)
    {
        $this->_mainTable = $table;
        return $this;
    }

    /**
     * Save object data
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\PurchaseOrderAbstract
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getForceObjectSave()) {
            parent::save($object);
        }

        return $this;
    }

    /**
     * Update grid table on entity update
     *
     * @param string $field
     * @param int $entityId
     * @return \Blackbox\EpaceImport\Model\Resource\PurchaseOrder\PurchaseOrderAbstract
     */
    public function updateOnRelatedRecordChanged($field, $entityId)
    {
        $adapter = $this->_getWriteAdapter();
        $column = array();
        $select = $adapter->select()
            ->from(array('main_table' => $this->getMainTable()), $column)
            ->where('main_table.' . $field .' = ?', $entityId);
        $this->joinVirtualGridColumnsToSelect('main_table', $select, $column);
        $fieldsToUpdate = $adapter->fetchRow($select);
        if ($fieldsToUpdate) {
            $adapter->update(
                $this->getGridTable(),
                $fieldsToUpdate,
                $adapter->quoteInto($this->getGridTable() . '.' . $field . ' = ?', $entityId)
            );
        }
        return $this;
    }
}

