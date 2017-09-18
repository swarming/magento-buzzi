<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

abstract class Buzzi_Consume_Model_Cron_HandleEventAbstract
{
    /**
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * @var \Buzzi_Consume_Model_Config_Events
     */
    protected $_configEvents;

    /**
     * @var \Buzzi_Consume_Model_Queue
     */
    protected $_queue;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_app = Mage::app();
        $this->_configEvents = Mage::getSingleton('buzzi_consume/config_events');
        $this->_queue = Mage::getModel('buzzi_consume/queue');
    }

    /**
     * @return string
     */
    abstract protected function _getEventType();

    /**
     * @return void
     */
    public function process()
    {
        $stores = $this->_app->getStores();

        foreach (array_keys($stores) as $storeId) {
            if (!$this->_configEvents->isEventEnabled($this->_getEventType(), $storeId)) {
                continue;
            }

            $this->_queue->handleByType($this->_getEventType(), $storeId);
        }
    }
}
