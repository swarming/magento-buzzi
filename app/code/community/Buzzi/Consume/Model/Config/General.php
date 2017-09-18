<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Model_Config_General extends Buzzi_Base_Model_Config_General
{
    const XML_PATH_ENABLED_CONSUME = 'buzzi_base/consume/enabled_consume';
    const XML_PATH_EVENTS = 'buzzi_base/consume/events';
    const XML_PATH_FETCH_TYPE = 'buzzi_base/consume/fetch_type';
    const XML_PATH_MAX_FETCH = 'buzzi_base/consume/max_fetch';

    const XML_PATH_CUSTOM_GLOBAL_SCHEDULE = 'buzzi_base/consume/custom_global_schedule';
    const XML_PATH_GLOBAL_SCHEDULE = 'buzzi_base/consume/global_schedule';
    const XML_PATH_GLOBAL_START_TIME = 'buzzi_base/consume/global_start_time';
    const XML_PATH_GLOBAL_FREQUENCY = 'buzzi_base/consume/global_frequency';

    const XML_PATH_REMOVE_IMMEDIATELY = 'buzzi_base/consume/remove_immediately';
    const XML_PATH_REMOVING_DELAY = 'buzzi_base/consume/removing_delay';

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return parent::isEnabled($storeId) && Mage::getStoreConfigFlag(self::XML_PATH_ENABLED_CONSUME, $this->_getStore($storeId));
    }

    /**
     * @param int|null $storeId
     * @return string[]
     */
    public function getEnabledEvents($storeId = null)
    {
        $eventTypes = Mage::getStoreConfig(self::XML_PATH_EVENTS, $this->_getStore($storeId));
        return $eventTypes ? explode(',', $eventTypes) : [];
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getFetchType($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_FETCH_TYPE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getMaxFetch($storeId = null)
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_MAX_FETCH, $storeId);
    }

    /**
     * @return bool
     */
    public function isCustomGlobalSchedule()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_CUSTOM_GLOBAL_SCHEDULE);
    }

    /**
     * @return string
     */
    public function getGlobalCronSchedule()
    {
        return Mage::getStoreConfig(self::XML_PATH_GLOBAL_SCHEDULE);
    }

    /**
     * @return int[]
     */
    public function getGlobalCronStartTime()
    {
        $time = Mage::getStoreConfig(self::XML_PATH_GLOBAL_START_TIME);
        return $time ? explode(',', $time) : [];
    }

    /**
     * @return string
     */
    public function getGlobalCronFrequency()
    {
        return Mage::getStoreConfig(self::XML_PATH_GLOBAL_FREQUENCY);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isRemoveImmediately($storeId = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_REMOVE_IMMEDIATELY, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getRemovingDelay($storeId = null)
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_REMOVING_DELAY, $storeId);
    }
}
