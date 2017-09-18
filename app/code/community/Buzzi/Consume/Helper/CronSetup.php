<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Helper_CronSetup
{
    /**
     * @var \Buzzi_Consume_Model_Config_General
     */
    protected $_configGeneral;

    /**
     * @var \Buzzi_Consume_Model_Config_Events
     */
    protected $_configEvents;

    /**
     * @var \Buzzi_Base_Helper_Config_Backend_CronSetup
     */
    protected $_backendCronSetupHelper;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configGeneral = Mage::getSingleton('buzzi_consume/config_general');
        $this->_configEvents = Mage::getSingleton('buzzi_consume/config_events');
        $this->_backendCronSetupHelper = Mage::helper('buzzi_base/config_backend_cronSetup');
    }

    /**
     * @param string $eventType
     * @return string
     */
    protected function _getJobName($eventType)
    {
        return sprintf('buzzi_consume_event_%s_handle', $this->_configEvents->getEventCode($eventType));
    }

    /**
     * @param string $eventType
     * @return void
     */
    public function setup($eventType)
    {
        if ($this->_configEvents->isGlobalSchedule($eventType)) {
            $this->_setupGlobal($eventType);
        } else {
            $this->_setupIndividual($eventType);
        }
    }

    /**
     * @param string $eventType
     * @return void
     */
    protected function _setupGlobal($eventType)
    {
        if ($this->_configGeneral->isCustomGlobalSchedule()) {
            $this->_setupGlobalCustomSchedule($eventType);
        } else {
            $this->_setupGlobalSchedule($eventType);
        }
    }

    /**
     * @param string $eventType
     * @return void
     */
    protected function _setupIndividual($eventType)
    {
        if ($this->_configEvents->isCustomSchedule($eventType)) {
            $this->_setupCustomSchedule($eventType);
        } else {
            $this->_setupSchedule($eventType);
        }
    }

    /**
     * @param string $eventType
     * @return void
     */
    protected function _setupGlobalCustomSchedule($eventType)
    {
        $cronSchedule = $this->_configGeneral->getGlobalCronSchedule();

        if (!empty($cronSchedule)) {
            $this->_backendCronSetupHelper->setupCustomSchedule($this->_getJobName($eventType), $cronSchedule);
        }
    }

    /**
     * @param string $eventType
     * @return void
     */
    protected function _setupGlobalSchedule($eventType)
    {
        $time = $this->_configGeneral->getGlobalCronStartTime();
        $frequency = $this->_configGeneral->getGlobalCronFrequency();

        if (!empty($time) && !empty($frequency)) {
            $this->_backendCronSetupHelper->setupSchedule($this->_getJobName($eventType), $time, $frequency);
        }
    }

    /**
     * @param string $eventType
     * @return void
     */
    protected function _setupCustomSchedule($eventType)
    {
        $cronSchedule = $this->_configEvents->getCronSchedule($eventType);

        if (!empty($cronSchedule)) {
            $this->_backendCronSetupHelper->setupCustomSchedule($this->_getJobName($eventType), $cronSchedule);
        }
    }

    /**
     * @param string $eventType
     * @return void
     */
    protected function _setupSchedule($eventType)
    {
        $time = $this->_configEvents->getCronStartTime($eventType);
        $frequency = $this->_configEvents->getCronFrequency($eventType);

        if (!empty($time) && !empty($frequency)) {
            $this->_backendCronSetupHelper->setupSchedule($this->_getJobName($eventType), $time, $frequency);
        }
    }
}
