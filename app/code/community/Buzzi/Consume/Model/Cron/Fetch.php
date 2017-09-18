<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Model_Cron_Fetch
{
    /**
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * @var \Buzzi_Consume_Model_Config_General
     */
    protected $_configGeneral;

    /**
     * @var \Buzzi_Consume_Model_Platform
     */
    protected $_platform;

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
        $this->_configGeneral = Mage::getModel('buzzi_consume/config_general');
        $this->_platform = Mage::getModel('buzzi_consume/platform');
        $this->_queue = Mage::getModel('buzzi_consume/queue');
    }

    /**
     * @return void
     */
    public function process()
    {
        $stores = $this->_app->getStores();

        foreach (array_keys($stores) as $storeId) {
            if (!$this->_configGeneral->isEnabled($storeId)) {
                continue;
            }

            $this->_fetchDeliveries($storeId);
        }
    }

    /**
     * @param int $storeId
     * @return void
     */
    protected function _fetchDeliveries($storeId)
    {
        try {
            $deliveries = $this->_platform->fetch($this->_configGeneral->getMaxFetch($storeId), $storeId);
            $this->_queue->addDeliveries($deliveries, $storeId);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
