<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Model_Cron_QueueCleaning
{
    /**
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * @var \Buzzi_Publish_Model_Config_General
     */
    protected $_configGeneral;

    /**
     * @var \Buzzi_Publish_Model_Queue
     */
    protected $_queue;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_app = Mage::app();
        $this->_configGeneral = Mage::getModel('buzzi_publish/config_general');
        $this->_queue = Mage::getModel('buzzi_publish/queue');
    }

    /**
     * @return void
     */
    public function process()
    {
        $stores = $this->_app->getStores();

        foreach (array_keys($stores) as $storeId) {
            $delay = $this->_configGeneral->isRemoveImmediately($storeId) ? 0 : $this->_configGeneral->getRemovingDelay($storeId);

            try {
                $this->_queue->deleteDone($delay, $storeId);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }
}
