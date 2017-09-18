<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Model_Config_System_Backend_Cron_Fetch extends Mage_Core_Model_Config_Data
{
    const JOB_NAME = 'buzzi_consume_fetch';

    const CRON_CUSTOM_SCHEDULE = 'groups/consume/fields/fetch_custom_schedule/value';
    const CRON_SCHEDULE = 'groups/consume/fields/fetch_schedule/value';
    const CRON_SCHEDULE_TIME = 'groups/consume/fields/fetch_start_time/value';
    const CRON_SCHEDULE_FREQUENCY = 'groups/consume/fields/fetch_frequency/value';

    /**
     * @var \Buzzi_Base_Helper_Config_Backend_CronSetup
     */
    protected $_backendCronSetupHelper;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_backendCronSetupHelper = Mage::helper('buzzi_base/config_backend_cronSetup');

        parent::_construct();
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function _afterSave()
    {
        $isCustomSchedule = $this->getData(self::CRON_CUSTOM_SCHEDULE);
        if ($isCustomSchedule) {
            $this->_setCustomSchedule();
        } else {
            $this->_setupSchedule();
        }

        return parent::_afterSave();
    }

    /**
     * @return void
     */
    protected function _setupSchedule()
    {
        $time = $this->getData(self::CRON_SCHEDULE_TIME);
        $frequency = $this->getData(self::CRON_SCHEDULE_FREQUENCY);

        if (!empty($time) && !empty($frequency)) {
            $this->_backendCronSetupHelper->setupSchedule(self::JOB_NAME, $time, $frequency);
        }
    }

    /**
     * @return void
     */
    protected function _setCustomSchedule()
    {
        $schedule = $this->getData(self::CRON_SCHEDULE);
        if (!empty($schedule)) {
            $this->_backendCronSetupHelper->setupCustomSchedule(self::JOB_NAME, $schedule);
        }
    }
}
