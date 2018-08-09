<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishCartPurchase_Model_Observer_OrderPlaceAfter
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
     * @var \Buzzi_PublishCartPurchase_Model_DataBuilder
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
        $this->_dataBuilder = Mage::getModel('buzzi_publish_cart_purchase/dataBuilder');
    }

    /**
     * @param \Varien_Event_Observer $observer
     * @return void
     */
    public function execute(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getData('order');
        $storeId = $order->getStoreId();

        if (!$this->_configEvents->isEventEnabled(Buzzi_PublishCartPurchase_Model_DataBuilder::EVENT_TYPE, $storeId)
            || !$this->_customerHelper->isCurrentExceptsMarketing()
        ) {
            return;
        }

        $payload = $this->_dataBuilder->getPayload($order);

        if ($this->_configEvents->isCron(Buzzi_PublishCartPurchase_Model_DataBuilder::EVENT_TYPE, $storeId)) {
            $this->_queue->add(Buzzi_PublishCartPurchase_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        } else {
            $this->_queue->send(Buzzi_PublishCartPurchase_Model_DataBuilder::EVENT_TYPE, $payload, $storeId);
        }
    }
}
