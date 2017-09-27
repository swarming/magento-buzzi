<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $this->getTable('buzzi_publish_cart_abandonment/cart_abandonment'),
    'error_message',
    Varien_Db_Ddl_Table::TYPE_TEXT
);
$installer->getConnection()->addColumn(
    $this->getTable('buzzi_publish_cart_abandonment/cart_abandonment'),
    'created_at',
    Varien_Db_Ddl_Table::TYPE_TIMESTAMP . ' NULL DEFAULT NULL'
);
$installer->getConnection()->addForeignKey(
    $installer->getFkName(
        'buzzi_publish_cart_abandonment/cart_abandonment',
        'quote_id',
        'sales_flat_quote',
        'entity_id'
    ),
    $installer->getTable('buzzi_publish_cart_abandonment/cart_abandonment'),
    'quote_id',
    $installer->getTable('sales_flat_quote'),
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

$installer->endSetup();
