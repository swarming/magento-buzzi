<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishCustomerLogout_Model_Observer_CustomerLogout
{
    /**
     * @var \Buzzi_Publish_Model_Config_Events
     */
    protected $_configEvents;

    /**
     * @var \Buzzi_Publish_Helper_Customer
     */
    protected $_customerHelper;

    /**
     * @var \Buzzi_Publish_Model_Queue
     */
    protected $_queue;

    /**
     * @var \Buzzi_PublishCustomerLogout_Model_DataBuilder
     */
    protected $_dataBuilder;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configEvents = Mage::getSingleton('buzzi_publish/config_events');
        $this->_customerHelper = Mage::helper('buzzi_publish/customer');
        $this->_queue = Mage::getModel('buzzi_publish/queue');
        $this->_dataBuilder = Mage::getModel('buzzi_publish_customer_logout/dataBuilder');
    }

    /**
     * @param \Varien_Event_Observer $observer
     * @return void
     */
    public function execute(Varien_Event_Observer $observer)
    {
        /** @var \Mage_Customer_Model_Customer $customer */
        $customer = $observer->getData('customer');
        $storeId = Mage::app()->getStore()->getId();

        if (!$this->_configEvents->isEventEnabled(Buzzi_PublishCustomerLogout_Model_DataBuilder::EVENT_TYPE, $storeId)
            || !$this->_customerHelper->isAcceptsMarketing($customer)
        ) {
            return;
        }

        $payload = $this->_dataBuilder->getPayload($customer);

        if ($this->_configEvents->isCron(Buzzi_PublishCustomerLogout_Model_DataBuilder::EVENT_TYPE, $storeId)) {
            $this->_queue->add(Buzzi_PublishCustomerLogout_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        } else {
            $this->_queue->send(Buzzi_PublishCustomerLogout_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        }
    }
}
