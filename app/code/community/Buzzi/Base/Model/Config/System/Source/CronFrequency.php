<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Base_Model_Config_System_Source_CronFrequency
{
    const CRON_EVERY_FIVE_MINUTES  = '5';
    const CRON_QUARTER_HOUR = 'Q';
    const CRON_HALF_HOUR = 'A';
    const CRON_HOURLY   = 'H';
    const CRON_DAILY    = 'D';
    const CRON_WEEKLY   = 'W';
    const CRON_MONTHLY  = 'M';

    /**
     * @var \Buzzi_Base_Helper_Data
     */
    protected $_helper;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('buzzi_base');
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => $this->_helper->__('Every 5 Minutes'),
                'value' => self::CRON_EVERY_FIVE_MINUTES
            ],
            [
                'label' => $this->_helper->__('Every 15 Minutes'),
                'value' => self::CRON_QUARTER_HOUR
            ],
            [
                'label' => $this->_helper->__('Every 30 Minutes'),
                'value' => self::CRON_HALF_HOUR
            ],
            [
                'label' => $this->_helper->__('Hourly'),
                'value' => self::CRON_HOURLY
            ],
            [
                'label' => $this->_helper->__('Daily'),
                'value' => self::CRON_DAILY,
            ],
            [
                'label' => $this->_helper->__('Weekly'),
                'value' => self::CRON_WEEKLY,
            ],
            [
                'label' => $this->_helper->__('Monthly'),
                'value' => self::CRON_MONTHLY,
            ],
        ];
    }
}
