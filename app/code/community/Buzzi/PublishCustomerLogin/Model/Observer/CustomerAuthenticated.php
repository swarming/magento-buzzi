<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishCustomerLogin_Model_Observer_CustomerAuthenticated
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
     * @var \Buzzi_PublishCustomerLogin_Model_DataBuilder
     */
    protected $_dataBuilder;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configEvents = Mage::getSingleton('buzzi_publish/config_events');
        $this->_queue = Mage::getModel('buzzi_publish/queue');
        $this->_dataBuilder = Mage::getModel('buzzi_publish_customer_login/dataBuilder');
    }

    /**
     * @param \Varien_Event_Observer $observer
     * @return void
     */
    public function execute(Varien_Event_Observer $observer)
    {
        /** @var \Mage_Customer_Model_Customer $customer */
        $customer = $observer->getData('model');
        $storeId = Mage::app()->getStore()->getId();

        if (!$this->_configEvents->isEventEnabled(Buzzi_PublishCustomerLogin_Model_DataBuilder::EVENT_TYPE, $storeId)) {
            return;
        }

        $payload = $this->_dataBuilder->getPayload($customer);

        if ($this->_configEvents->isCron(Buzzi_PublishCustomerLogin_Model_DataBuilder::EVENT_TYPE, $storeId)) {
            $this->_queue->add(Buzzi_PublishCustomerLogin_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        } else {
            $this->_queue->send(Buzzi_PublishCustomerLogin_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        }
    }
}
