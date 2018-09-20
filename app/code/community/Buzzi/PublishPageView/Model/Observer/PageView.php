<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishPageView_Model_Observer_PageView
{
    /**
     * @var \Buzzi_Publish_Model_Config_Events
     */
    private $_configEvents;

    /**
     * @var \Buzzi_Publish_Helper_Customer
     */
    private $_customerHelper;

    /**
     * @var \Buzzi_Publish_Model_Queue
     */
    private $_queue;

    /**
     * @var \Buzzi_PublishPageView_Model_DataBuilder
     */
    private $_dataBuilder;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configEvents = Mage::getSingleton('buzzi_publish/config_events');
        $this->_customerHelper = Mage::helper('buzzi_publish/customer');
        $this->_queue = Mage::getModel('buzzi_publish/queue');
        $this->_dataBuilder = Mage::getModel('buzzi_publish_page_view/dataBuilder');
    }

    /**
     * @return \Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * @param \Varien_Event_Observer $observer
     * @return void
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $storeId = Mage::app()->getStore()->getId();

        if (!$this->_configEvents->isEventEnabled(Buzzi_PublishPageView_Model_DataBuilder::EVENT_TYPE, $storeId)
            || !$this->_getCustomerSession()->isLoggedIn()
            || !$this->_customerHelper->isCurrentAcceptsMarketing()
        ) {
            return;
        }

        /** @var \Mage_Core_Controller_Front_Action $controller */
        $controller = $observer->getData('controller_action');

        $customer = $this->_getCustomerSession()->getCustomer();

        $payload = $this->_dataBuilder->getPayload(
            $customer,
            $this->getWebsiteCode(),
            $controller->getFullActionName('-'),
            $this->getCurrentCategory()
        );

        if ($this->_configEvents->isCron(Buzzi_PublishPageView_Model_DataBuilder::EVENT_TYPE, $storeId)) {
            $this->_queue->add(Buzzi_PublishPageView_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        } else {
            $this->_queue->send(Buzzi_PublishPageView_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        }
    }

    /**
     * @return string
     */
    private function getWebsiteCode()
    {
        return Mage::app()->getStore()->getWebsite()->getCode();
    }

    /**
     * @return \Mage_Catalog_Model_Category|null
     */
    private function getCurrentCategory()
    {
        return Mage::registry('current_category');
    }
}
