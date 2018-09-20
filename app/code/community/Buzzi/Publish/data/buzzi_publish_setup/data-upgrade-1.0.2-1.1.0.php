<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$setup = new Mage_Customer_Model_Resource_Setup('core_setup');

if ($setup->getAttribute('customer', \Buzzi_Publish_Helper_Customer::ATTR_EXCEPTS_MARKETING)) {
    $setup->removeAttribute('customer', \Buzzi_Publish_Helper_Customer::ATTR_EXCEPTS_MARKETING);
}

$setup->addAttribute('customer', \Buzzi_Publish_Helper_Customer::ATTR_EXCEPTS_MARKETING, [
    'label'      => 'Accepts Marketing',
    'type'       => 'int',
    'input'      => 'select',
    'source'     => 'eav/entity_attribute_source_boolean',
    'visible'    => true,
    'required'   => false,
    'default'    => '1',
    'sort_order' => '120',
    'position'   => '120',
]);

/** @var \Mage_Eav_Model_Config $eavConfig */
$eavConfig = Mage::getSingleton('eav/config');
$attribute = $eavConfig->getAttribute('customer', \Buzzi_Publish_Helper_Customer::ATTR_EXCEPTS_MARKETING);
$attribute->setData('used_in_forms', ['adminhtml_customer']);
$attribute->save();

/** @var Mage_Customer_Model_Resource_Customer_Collection $customerCollection */
$customerCollection = Mage::getResourceModel('customer/customer_collection');
$customerCollection->setDataToAll(\Buzzi_Publish_Helper_Customer::ATTR_EXCEPTS_MARKETING, 1);
$customerCollection->save();

$installer->endSetup();
