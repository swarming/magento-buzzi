<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Model_Cron_ResendFailed
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
            if (!$this->_configGeneral->isEnabled($storeId) || !$this->_configGeneral->isResendEnable($storeId)) {
                continue;
            }

            try {
                $this->_queue->resendFailed($storeId);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }
}
