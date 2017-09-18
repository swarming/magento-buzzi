<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Model_Config_Events
{
    const TYPE = 'publish';

    /**
     * @var \Buzzi_Publish_Model_Config_General
     */
    protected $_configGeneral;

    /**
     * @var mixed[]
     */
    protected $_eventsConfigData;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configGeneral = Mage::getModel('buzzi_publish/config_general');

        /** @var \Buzzi_Base_Model_Config_Structure_EventConfigReader $configEventReader */
        $configEventReader = Mage::getModel('buzzi_base/config_structure_eventConfigReader');
        $this->_eventsConfigData = $configEventReader->getEventsConfig(self::TYPE);
    }

    /**
     * @return string[]
     */
    public function getAllTypes()
    {
        return array_keys($this->_eventsConfigData);
    }

    /**
     * @param string $type
     * @param int|string|null $storeId
     * @return bool
     */
    public function isEventEnabled($type, $storeId = null)
    {
        return $this->_configGeneral->isEnabled($storeId)
            && isset($this->_eventsConfigData[$type])
            && in_array($type, $this->_configGeneral->getEnabledEvents($storeId));
    }

    /**
     * @param string $type
     * @return string
     */
    public function getEventCode($type)
    {
        return $this->_getEventConfigValue($type, 'code');
    }

    /**
     * @param string $type
     * @return string
     */
    public function getEventLabel($type)
    {
        return $this->_getEventConfigValue($type, 'label') ?: $type;
    }

    /**
     * @param string $type
     * @param int|string|null $storeId
     * @return bool
     */
    public function isCron($type, $storeId = null)
    {
        return $this->getFlag($type, 'is_cron', $storeId) || $this->isCronOnly($type);
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isCronOnly($type)
    {
        return (bool)$this->_getEventConfigValue($type, 'cron_only');
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isGlobalSchedule($type)
    {
        return (bool)$this->getValue($type, 'global_schedule');
    }

    /**
     * @param string $type
     * @return bool
     */
    public function isCustomSchedule($type)
    {
        return (bool)$this->getValue($type, 'custom_schedule');
    }

    /**
     * @param string $type
     * @return string
     */
    public function getCronSchedule($type)
    {
        return $this->getValue($type, 'cron_schedule');
    }

    /**
     * @param string $type
     * @return int[]
     */
    public function getCronStartTime($type)
    {
        $time = $this->getValue($type, 'cron_start_time');
        return $time ? explode(',', $time) : [];
    }

    /**
     * @param string $type
     * @return string
     */
    public function getCronFrequency($type)
    {
        return $this->getValue($type, 'cron_frequency');
    }

    /**
     * @param string $type
     * @param string $field
     * @param int|string|null $storeId
     * @return string
     */
    public function getValue($type, $field, $storeId = null)
    {
        return Mage::getStoreConfig(sprintf('buzzi_publish_events/%s/%s', $this->getEventCode($type), $field), $storeId);
    }

    /**
     * @param string $type
     * @param string $field
     * @param int|string|null $storeId
     * @return string
     */
    public function getFlag($type, $field, $storeId = null)
    {
        return Mage::getStoreConfigFlag(sprintf('buzzi_publish_events/%s/%s', $this->getEventCode($type), $field), $storeId);
    }

    /**
     * @param string $type
     * @param string $code
     * @return mixed|null
     * @throws \DomainException
     */
    protected function _getEventConfigValue($type, $code)
    {
        if (!isset($this->_eventsConfigData[$type])) {
            throw new \DomainException(sprintf('"%s" publish event is not supported.', $type));
        }

        return isset($this->_eventsConfigData[$type][$code])
            ? $this->_eventsConfigData[$type][$code]
            : null;
    }
}
