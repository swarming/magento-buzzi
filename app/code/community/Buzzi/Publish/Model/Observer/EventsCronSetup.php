<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Model_Observer_EventsCronSetup
{
    /**
     * @var \Buzzi_Publish_Model_Config_Events
     */
    protected $_configEvents;

    /**
     * @var \Buzzi_Publish_Helper_EventsCronSetup
     */
    protected $_cronSetupHelper;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configEvents = Mage::getSingleton('buzzi_publish/config_events');
        $this->_cronSetupHelper = Mage::helper('buzzi_publish/eventsCronSetup');
    }

    /**
     * @param \Varien_Event_Observer $event
     * @return void
     */
    public function execute($event)
    {
        foreach ($this->_configEvents->getAllTypes() as $eventType) {
            $this->_cronSetupHelper->setup($eventType);
        }

        Mage::getConfig()->reinit();
    }
}
