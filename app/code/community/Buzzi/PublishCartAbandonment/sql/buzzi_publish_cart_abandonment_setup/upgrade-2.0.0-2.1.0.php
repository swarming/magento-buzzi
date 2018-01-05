<?php

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$cartAbandonmentTable = $installer->getTable('buzzi_publish_cart_abandonment/cart_abandonment');
$installer->getConnection()->addColumn($cartAbandonmentTable, 'fingerprint',
    [
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 32,
        'after'   => 'quote_id',
        'comment' => 'Quote fingerprint md5 from quoteId, productIds with qty'
    ]);

$installer->run("UPDATE {$cartAbandonmentTable} as sbca
   SET fingerprint = md5(CONCAT(sbca.quote_id, (SELECT GROUP_CONCAT(sqi.product_id, sqi.qty ORDER BY sqi.product_id ASC) FROM sales_flat_quote_item as sqi WHERE sbca.quote_id = sqi.quote_id GROUP BY sqi.quote_id)))"
);

$installer->getConnection()->dropForeignKey(
    $cartAbandonmentTable,
    $installer->getFkName(
        'buzzi_publish_cart_abandonment/cart_abandonment',
        'quote_id',
        'sales_flat_quote',
        'entity_id'
    )
);
$installer->getConnection()->dropIndex(
    $cartAbandonmentTable,
    $installer->getIdxName($cartAbandonmentTable, ['quote_id'], Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
);

$installer->getConnection()->addIndex(
    $cartAbandonmentTable,
    $installer->getIdxName($cartAbandonmentTable, ['fingerprint'], Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
    ['fingerprint'],
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
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
