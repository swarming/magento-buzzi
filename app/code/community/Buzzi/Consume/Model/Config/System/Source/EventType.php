<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Model_Config_System_Source_EventType
{
    /**
     * @var \Buzzi_Consume_Model_Config_Events
     */
    protected $_configEvents;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configEvents = Mage::getSingleton('buzzi_consume/config_events');
        $this->_helper = Mage::helper('buzzi_consume');
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->_configEvents->getAllTypes() as $eventType) {
            $options[] = [
                'label' => $this->_configEvents->getEventLabel($eventType),
                'value' => $eventType,
            ];
        }
        return $options;
    }

    /**
     * @return array
     */
    public function toOptionHash()
    {
        $options = [];
        foreach ($this->_configEvents->getAllTypes() as $eventType) {
            $options[$eventType] = $this->_configEvents->getEventLabel($eventType);
        }
        return $options;
    }
}
