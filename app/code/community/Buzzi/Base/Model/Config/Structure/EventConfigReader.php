<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Base_Model_Config_Structure_EventConfigReader
{
    const CONFIG_NAMESPACE = 'buzzi';

    /**
     * @var Mage_Core_Model_Config_Element
     */
    protected $_eventConfig;

    /**
     * @return Mage_Core_Model_Config
     */
    protected function _getMageConfig()
    {
        return Mage::getConfig();
    }

    /**
     * @param string $eventsType
     * @return array
     */
    public function getEventsConfig($eventsType)
    {
        $eventsConfig = [];
        foreach ($this->getArray($eventsType) as $code => $eventConfig) {
            $eventsConfig[$eventConfig['type']] = $eventConfig;
            $eventsConfig[$eventConfig['type']]['code'] = $code;
        }
        return $eventsConfig;
    }

    /**
     * @param string $eventsType
     * @return array
     */
    public function getArray($eventsType)
    {
        return (array) $this->_getEventConfig()->{$eventsType} ? $this->_getEventConfig()->{$eventsType}->asArray() : [];
    }

    /**
     * @return Mage_Core_Model_Config_Element
     */
    protected function _getEventConfig()
    {
        if (null === $this->_eventConfig) {
            $this->_eventConfig = $this->_getMageConfig()->getNode(Mage_Core_Model_App_Area::AREA_GLOBAL)->{self::CONFIG_NAMESPACE};
        }
        return $this->_eventConfig;
    }
}
