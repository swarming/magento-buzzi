<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'buzzi_consume_queue'
 */
$consumeQueueTable = $installer->getConnection()
    ->newTable($installer->getTable('buzzi_consume/queue'))
    ->addColumn(
        'delivery_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false, 'primary' => true, 'auto_increment' => true],
        'Id'
    )
    ->addColumn(
        'store_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        ['unsigned' => true, 'nullable' => false],
        'Store id'
    )
    ->addColumn(
        'event_type',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        50,
        ['nullable' => false, 'default' => ''],
        'Event Type'
    )
    ->addColumn(
        'use_file',
        Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        null,
        ['nullable' => false, 'default' => '0'],
        'Whether payload field is file name'
    )
    ->addColumn(
        'payload',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        Buzzi_Consume_Model_Delivery::MAX_PAYLOAD_LENGTH,
        ['nullable' => false, 'default' => ''],
        'Payload'
    )
    ->addColumn(
        'counter',
        Varien_Db_Ddl_Table::TYPE_TINYINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Counter'
    )
    ->addColumn(
        'receipt',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        null,
        ['nullable' => true],
        'Receipt'
    )
    ->addColumn(
        'delivery_time',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        ['nullable' => false],
        'Delivery time'
    )
    ->addColumn(
        'handle_time',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        ['nullable' => true],
        'Handle time'
    )
    ->addColumn(
        'is_confirmed',
        Varien_Db_Ddl_Table::TYPE_TINYINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => 0],
        'Is Confirmed'
    )
    ->addColumn(
        'status',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        10,
        ['nullable' => false, 'default' => Buzzi_Consume_Model_Delivery::STATUS_PENDING],
        'Status'
    )
    ->addColumn(
        'error_message',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        null,
        ['nullable' => true],
        'Error message'
    )
    ->addIndex(
        $installer->getIdxName(
            $installer->getTable('buzzi_consume/queue'),
            ['store_id'],
            Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
        ),
        ['store_id'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX]
    )
    ->addIndex(
        $installer->getIdxName(
            $installer->getTable('buzzi_consume/queue'),
            ['event_type'],
            Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
        ),
        ['event_type'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX]
    )
    ->addIndex(
        $installer->getIdxName(
            $installer->getTable('buzzi_consume/queue'),
            ['status'],
            Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
        ),
        ['status'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX]
    )
    ->addIndex(
        $installer->getIdxName(
            $installer->getTable('buzzi_consume/queue'),
            ['handle_time'],
            Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
        ),
        ['handle_time'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX]
    )
    ->setComment('Buzzi Consume Queue');
$installer->getConnection()->createTable($consumeQueueTable);

$installer->endSetup();
