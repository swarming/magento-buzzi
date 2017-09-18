<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Model_Observer_EventsCronSetup
{
    /**
     * @var \Buzzi_Consume_Model_Config_Events
     */
    protected $_configEvents;

    /**
     * @var \Buzzi_Consume_Helper_CronSetup
     */
    protected $_consumeCronSetup;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configEvents = Mage::getSingleton('buzzi_consume/config_events');
        $this->_consumeCronSetup = Mage::helper('buzzi_consume/cronSetup');
    }

    /**
     * @param \Varien_Event_Observer $event
     * @return void
     */
    public function execute($event)
    {
        foreach ($this->_configEvents->getAllTypes() as $eventType) {
            $this->_consumeCronSetup->setup($eventType);
        }

        Mage::getConfig()->reinit();
    }
}
