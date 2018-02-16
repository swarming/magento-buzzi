<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Model_Config_General extends Buzzi_Base_Model_Config_General
{
    const XML_PATH_ENABLED_PUBLISH = 'buzzi_base/publish/enabled_publish';
    const XML_PATH_EVENTS = 'buzzi_base/publish/events';

    const XML_PATH_RESEND_ENABLE = 'buzzi_base/publish/resend_enable';
    const XML_PATH_RESEND_MAX_TIME = 'buzzi_base/publish/resend_max_time';

    const XML_PATH_PAYLOAD_USE_ORG_PRODUCT_IMAGE = 'buzzi_base/publish/use_original_product_images';

    const XML_PATH_CUSTOM_GLOBAL_SCHEDULE = 'buzzi_base/publish/custom_global_schedule';
    const XML_PATH_GLOBAL_SCHEDULE = 'buzzi_base/publish/global_schedule';
    const XML_PATH_GLOBAL_START_TIME = 'buzzi_base/publish/global_start_time';
    const XML_PATH_GLOBAL_FREQUENCY = 'buzzi_base/publish/global_frequency';

    const XML_PATH_REMOVE_IMMEDIATELY = 'buzzi_base/publish/remove_immediately';
    const XML_PATH_REMOVING_DELAY = 'buzzi_base/publish/removing_delay';

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return parent::isEnabled($storeId) && Mage::getStoreConfigFlag(self::XML_PATH_ENABLED_PUBLISH, $this->_getStore($storeId));
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
     * @return bool
     */
    public function isUseOriginalProductImages($storeId = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_PAYLOAD_USE_ORG_PRODUCT_IMAGE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isResendEnable($storeId = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_RESEND_ENABLE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getResendMaxTime($storeId = null)
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_RESEND_MAX_TIME, $storeId);
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
