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
    'text'
);

$installer->endSetup();
