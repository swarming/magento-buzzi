<?php

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$logCustomerTable = $installer->getTable('log/customer');
$installer->getConnection()->dropIndex($logCustomerTable, $installer->getIdxName($logCustomerTable, ['customer_id'], Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX));

$logVisitorTable = $installer->getTable('log/visitor');
$installer->getConnection()->dropIndex($logVisitorTable, $installer->getIdxName($logVisitorTable, ['last_visit_at'], Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX));

$cartAbandonmentTable = $installer->getTable('buzzi_publish_cart_abandonment/cart_abandonment');
$installer->getConnection()->dropIndex($cartAbandonmentTable, $installer->getIdxName($cartAbandonmentTable, ['store_id'], Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX));
$installer->getConnection()->dropIndex($cartAbandonmentTable, $installer->getIdxName($cartAbandonmentTable, ['status'], Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX));
