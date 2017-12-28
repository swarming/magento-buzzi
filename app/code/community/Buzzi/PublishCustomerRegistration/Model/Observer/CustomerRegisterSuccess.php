<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishCustomerRegistration_Model_Observer_CustomerRegisterSuccess
{
    /**
     * @var \Buzzi_Publish_Model_Config_Events
     */
    protected $_configEvents;

    /**
     * @var \Buzzi_Publish_Model_Queue
     */
    protected $_queue;

    /**
     * @var \Buzzi_PublishCustomerRegistration_Model_DataBuilder
     */
    protected $_dataBuilder;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configEvents = Mage::getSingleton('buzzi_publish/config_events');
        $this->_queue = Mage::getModel('buzzi_publish/queue');
        $this->_dataBuilder = Mage::getModel('buzzi_publish_customer_registration/dataBuilder');
    }

    /**
     * @param \Varien_Event_Observer $observer
     * @return void
     */
    public function execute(Varien_Event_Observer $observer)
    {
        /** @var \Mage_Customer_Model_Customer $customer */
        $customer = $observer->getData('customer');
        $currentStore = Mage::app()->getStore();
        $storeId = $currentStore->isAdmin() ? $customer->getStoreId() : $currentStore->getId();

        if (!$this->_configEvents->isEventEnabled(Buzzi_PublishCustomerRegistration_Model_DataBuilder::EVENT_TYPE, $storeId)
            || ($currentStore->isAdmin() && !$this->_configEvents->getValue(Buzzi_PublishCustomerRegistration_Model_DataBuilder::EVENT_TYPE, 'track_admin_created'))
        ) {
            return;
        }

        $payload = $this->_dataBuilder->getPayload($customer);

        if ($this->_configEvents->isCron(Buzzi_PublishCustomerRegistration_Model_DataBuilder::EVENT_TYPE, $storeId)) {
            $this->_queue->add(Buzzi_PublishCustomerRegistration_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        } else {
            $this->_queue->send(Buzzi_PublishCustomerRegistration_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        }
    }
}
