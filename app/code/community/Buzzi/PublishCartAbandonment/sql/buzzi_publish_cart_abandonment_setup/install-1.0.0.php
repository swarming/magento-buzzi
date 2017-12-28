<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'buzzi_publish_cart_abandonment'
 */
$cartAbandonmentTable = $installer->getTable('buzzi_publish_cart_abandonment/cart_abandonment');
$table = $installer->getConnection()
    ->newTable($cartAbandonmentTable)
    ->addColumn('abandonment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'auto_increment' => true,
    ], 'Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false,
    ], 'Store ID')
    ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false,
    ], 'Quote Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'nullable' => false,
    ], 'Customer Id')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 10, [
        'nullable' => false,
        'default' => Buzzi_PublishCartAbandonment_Model_CartAbandonment::STATUS_PENDING
    ], 'Status')
    ->addIndex(
        $installer->getIdxName($cartAbandonmentTable, ['store_id'], Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX),
        ['store_id'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX]
    )
    ->addIndex(
        $installer->getIdxName($cartAbandonmentTable, ['quote_id'], Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        ['quote_id'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE]
    )
    ->addIndex(
        $installer->getIdxName($cartAbandonmentTable, ['status'], Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX),
        ['status'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX]
    )
    ->setComment('Buzzi Publish Cart Abandonment');
$installer->getConnection()->createTable($table);


/**
 * Additional indexes on sales_flat_quote, log_customer and log_visitor tables
 */
$installer->getConnection()->addIndex(
    $installer->getTable('sales/quote'),
    $installer->getIdxName($installer->getTable('sales/quote'), ['updated_at']),
    ['updated_at']
);

$installer->endSetup();
