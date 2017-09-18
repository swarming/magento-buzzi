<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Base_Helper_Config_Backend_CronSetup
{
    /**
     * @return Mage_Core_Model_Config_Data
     */
    protected function _createConfigDataModel()
    {
        return Mage::getModel('core/config_data');
    }

    /**
     * @param string $jobName
     * @return string
     */
    protected function _getScheduleConfigPath($jobName)
    {
        return sprintf('crontab/jobs/%s/schedule/cron_expr', $jobName);
    }

    /**
     * @param string $jobName
     * @param string $cronExprString
     * @return void
     */
    public function setupCustomSchedule($jobName, $cronExprString)
    {
        $this->_saveConfig($this->_getScheduleConfigPath($jobName), $cronExprString);
    }

    /**
     * @param string $jobName
     * @param int[] $time
     * @param string $frequency
     * @return void
     */
    public function setupSchedule($jobName, $time, $frequency)
    {
        $cronExprString = $this->_getCronExpr($time, $frequency);
        $this->setupCustomSchedule($jobName, $cronExprString);
    }

    /**
     * @param int[] $time
     * @param string $frequency
     * @return string
     */
    protected function _getCronExpr($time, $frequency)
    {
        $hours = intval($time[0]);
        $minutes = intval($time[1]);

        $cronExprArray = [
            $this->_calculateMinutes($minutes, $frequency),                                                # Minute
            $this->_calculateHours($hours),                                                                # Hour
            ($frequency == Buzzi_Base_Model_Config_System_Source_CronFrequency::CRON_MONTHLY) ? '1' : '*', # Day of the Month
            '*',                                                                                           # Month of the Year
            ($frequency == Buzzi_Base_Model_Config_System_Source_CronFrequency::CRON_WEEKLY) ? '1' : '*',  # Day of the Week
        ];

        return join(' ', $cronExprArray);
    }

    /**
     * @param int $selectedHours
     * @return string
     */
    protected function _calculateHours($selectedHours)
    {
        return $selectedHours === 0 ? '*' : $selectedHours . '-23';
    }

    /**
     * @param int $selectedMinutes
     * @param string $frequency
     * @return string
     */
    protected function _calculateMinutes($selectedMinutes, $frequency)
    {
        $minutes = $selectedMinutes === 0 ? '*' : $selectedMinutes . '-59';

        switch ($frequency) {
            case Buzzi_Base_Model_Config_System_Source_CronFrequency::CRON_HALF_HOUR:
                $minutes .= '/30';
                break;
            case Buzzi_Base_Model_Config_System_Source_CronFrequency::CRON_QUARTER_HOUR:
                $minutes .= '/15';
                break;
            case Buzzi_Base_Model_Config_System_Source_CronFrequency::CRON_EVERY_FIVE_MINUTES:
                $minutes .= '/5';
                break;
        }

        return $minutes;
    }

    /**
     * @param string $configPath
     * @param string $value
     * @return void
     * @throws \Exception
     */
    protected function _saveConfig($configPath, $value)
    {
        try {
            $this->_createConfigDataModel()
                ->load($configPath, 'path')
                ->setValue($value)
                ->setPath($configPath)
                ->save();
        } catch (Exception $e) {
            throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));
        }
    }
}
