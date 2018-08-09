<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishProductView_Model_Observer_ProductView
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
     * @var \Buzzi_PublishProductView_Model_DataBuilder
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
        $this->_dataBuilder = Mage::getModel('buzzi_publish_product_view/dataBuilder');
    }

    /**
     * @return \Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * @return \Mage_Catalog_Model_Product
     */
    protected function _createProductModel()
    {
        return Mage::getModel('catalog/product');
    }

    /**
     * @param \Varien_Event_Observer $observer
     * @return void
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $storeId = Mage::app()->getStore()->getId();

        if (!$this->_configEvents->isEventEnabled(Buzzi_PublishProductView_Model_DataBuilder::EVENT_TYPE, $storeId)
            || !$this->_getCustomerSession()->isLoggedIn()
            || !$this->_customerHelper->isCurrentExceptsMarketing()
        ) {
            return;
        }

        /** @var Mage_Catalog_ProductController $controller */
        $controller = $observer->getData('controller_action');
        $productId  = (int) $controller->getRequest()->getParam('id');

        $product = $this->_loadProduct($productId, $storeId);
        if (!$product->getId()) {
            return;
        }

        $customer = $this->_getCustomerSession()->getCustomer();

        $payload = $this->_dataBuilder->getPayload($customer, $product);

        if ($this->_configEvents->isCron(Buzzi_PublishProductView_Model_DataBuilder::EVENT_TYPE, $storeId)) {
            $this->_queue->add(Buzzi_PublishProductView_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        } else {
            $this->_queue->send(Buzzi_PublishProductView_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        }
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @return \Mage_Catalog_Model_Product
     */
    protected function _loadProduct($productId, $storeId)
    {
        $product = $this->_createProductModel();
        $product->setStoreId($storeId);
        $product->load($productId);
        return $product;
    }
}
