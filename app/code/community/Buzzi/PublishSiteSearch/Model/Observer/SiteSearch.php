<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */


class Buzzi_PublishSiteSearch_Model_Observer_SiteSearch
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
     * @var \Buzzi_PublishSiteSearch_Model_DataBuilder
     */
    protected $_dataBuilder;

    /**
     * @var \Mage_Core_Controller_Request_Http
     */
    protected $_request;

    /**
     * @var \Mage_CatalogSearch_Helper_Data
     */
    protected $_catalogSearchHelper;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configEvents = Mage::getSingleton('buzzi_publish/config_events');
        $this->_queue = Mage::getModel('buzzi_publish/queue');
        $this->_dataBuilder = Mage::getModel('buzzi_publish_site_search/dataBuilder');
        $this->_request = Mage::app()->getRequest();
        $this->_catalogSearchHelper = Mage::helper('catalogsearch');
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

        if (!$this->_configEvents->isEventEnabled(Buzzi_PublishSiteSearch_Model_DataBuilder::EVENT_TYPE, $storeId)
            ||
            !$this->_getCustomerSession()->isLoggedIn()
        ) {
            return;
        }

        $refererUrl = $this->_request->getServer('HTTP_REFERER');

        $searchQuery = $this->_catalogSearchHelper->getQuery()->getQueryText();
        $searchQuery = trim($searchQuery);
        if (empty($searchQuery) || empty($refererUrl)) {
            return;
        }

        $searchData = [
            'search_query' => $searchQuery,
            'page_url' => $refererUrl
        ];

        $customer = $this->_getCustomerSession()->getCustomer();

        $payload = $this->_dataBuilder->getPayload($customer, $searchData);

        if ($this->_configEvents->isCron(Buzzi_PublishSiteSearch_Model_DataBuilder::EVENT_TYPE, $storeId)) {
            $this->_queue->add(Buzzi_PublishSiteSearch_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        } else {
            $this->_queue->send(Buzzi_PublishSiteSearch_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        }
    }
}
