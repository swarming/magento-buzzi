<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'buzzi_publish_queue'
 */
$publishQueueTable = $installer->getConnection()
    ->newTable($installer->getTable('buzzi_publish/queue'))
    ->addColumn(
        'submission_id',
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
        ['nullable' => false],
        'Event Type'
    )
    ->addColumn(
        'payload',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        Buzzi_Publish_Model_Submission::MAX_PAYLOAD_LENGTH,
        ['nullable' => false, 'default' => ''],
        'Payload'
    )
    ->addColumn(
        'use_file',
        Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        null,
        ['nullable' => false, 'default' => '0'],
        'Whether payload field is file name'
    )
    ->addColumn(
        'counter',
        Varien_Db_Ddl_Table::TYPE_TINYINT,
        null,
        ['unsigned' => true, 'nullable' => false, 'default' => '0'],
        'Counter'
    )
    ->addColumn(
        'event_id',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        50,
        ['nullable' => true],
        'Message id'
    )
    ->addColumn(
        'creating_time',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        ['nullable' => false],
        'Creating time'
    )
    ->addColumn(
        'submission_time',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        ['nullable' => true],
        'Submission time'
    )
    ->addColumn(
        'status',
        Varien_Db_Ddl_Table::TYPE_VARCHAR,
        10,
        ['nullable' => false, 'default' => Buzzi_Publish_Model_Submission::STATUS_PENDING],
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
            $installer->getTable('buzzi_publish/queue'),
            ['store_id'],
            Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
        ),
        ['store_id'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX]
    )
    ->addIndex(
        $installer->getIdxName(
            $installer->getTable('buzzi_publish/queue'),
            ['event_type'],
            Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
        ),
        ['event_type'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX]
    )
    ->addIndex(
        $installer->getIdxName(
            $installer->getTable('buzzi_publish/queue'),
            ['status'],
            Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
        ),
        ['status'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX]
    )
    ->setComment('Buzzi Publish Queue');

$installer->getConnection()->createTable($publishQueueTable);

$installer->endSetup();
