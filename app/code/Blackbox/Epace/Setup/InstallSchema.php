<?php

namespace Blackbox\Epace\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('epace_event')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('epace_event')
			)
				->addColumn(
					'id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'nullable' => false,
						'primary'  => true,
						'unsigned' => true,
					],
					'Event ID'
				)
				->addColumn(
					'name',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					100,
					['nullable => false'],
					'Event name'
				)
				->addColumn(
					'processed_time',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					100,
					[],
					'Event processed_time'
				)
				->addColumn(
					'status',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'100',
					[],
					'Event status'
				)
				->addColumn(
					'host',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					100,
					[],
					'Event host'
				)
				->addColumn(
					'username',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					100,
					[],
					'Event username'
				)
				->addColumn(
					'password',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					100,
					[],
					'Event password'
				)
				->addColumn(
					'mode',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					100,
					[],
					'Event mode'
				)->addColumn(
					'serialized_data',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					100,
					[],
					'Event serialized_data')
				->setComment('Event Table');
				
			$installer->getConnection()->createTable($table);

			
		}
		
		if (!$installer->tableExists('epace_event_file')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('epace_event_file')
			)
				->addColumn(
					'id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'nullable' => false,
						'primary'  => true,
						'unsigned' => true,
					],
					'File ID'
				)
				->addColumn(
					'event_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null,
					[
					'nullable' => false,
					],
					'File event_id'
				)
				->addColumn(
					'type',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					100,
					[],
					'File type'
				)
				->addColumn(
					'action',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'100',
					[],
					'File action'
				)
				->addColumn(
					'path',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					100,
					[],
					'File path'
				)
				->addColumn(
					'related_file_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[],
					'File related_file_id'
				)
				->setComment('Event Table');
				
			$installer->getConnection()->createTable($table);

			
		}
		$installer->endSetup();
	}
}