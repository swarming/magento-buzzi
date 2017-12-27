<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishCartAbandonment_Model_Cron_Submit
{
    /**
     * @var \Mage_Core_Model_App
     */
    protected $_app;

    /**
     * @var \Buzzi_Publish_Model_Config_Events
     */
    protected $_configEvents;

    /**
     * @var \Buzzi_PublishCartAbandonment_Model_Indexer
     */
    protected $_cartAbandonmentIndexer;

    /**
     * @var \Buzzi_PublishCartAbandonment_Model_Manager
     */
    protected $_cartAbandonmentManager;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_app = Mage::app();
        $this->_configEvents = Mage::getSingleton('buzzi_publish/config_events');
        $this->_cartAbandonmentIndexer = Mage::getModel('buzzi_publish_cart_abandonment/indexer');
        $this->_cartAbandonmentManager = Mage::getModel('buzzi_publish_cart_abandonment/manager');
    }

    /**
     * @return void
     */
    public function process()
    {
        $originStore = $this->_app->getStore();
        $stores = $this->_app->getStores();

        /** @var Mage_Core_Model_Store $store */
        foreach ($stores as $store) {
            $storeId = $store->getId();
            if (!$this->_configEvents->isEventEnabled(Buzzi_PublishCartAbandonment_Model_DataBuilder::EVENT_TYPE, $storeId)) {
                continue;
            }

            $this->_app->setCurrentStore($store->getCode());

            $this->_cartAbandonmentIndexer->reindex(
                $this->_configEvents->getValue(Buzzi_PublishCartAbandonment_Model_DataBuilder::EVENT_TYPE, 'quote_last_action', $storeId),
                $storeId,
                $this->_configEvents->getValue(Buzzi_PublishCartAbandonment_Model_DataBuilder::EVENT_TYPE, 'quotes_limit', $storeId)
            );

            $this->_cartAbandonmentManager->sendPending($storeId);
        }

        $this->_app->setCurrentStore($originStore->getCode());
    }
}
