<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Base_Model_Observer_CheckCron
{
    const CRON_DELTA = 3600;

    /**
     * @var \Buzzi_Base_Helper_Data
     */
    protected $_helper;

    /**
     * @var string[]
     */
    protected $_buzziSections = [
        'buzzi_base',
        'buzzi_publish_events',
        'buzzi_consume_events'
    ];

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('buzzi_base');
    }

    /**
     * @return void
     */
    public function execute()
    {
        $currentSection = Mage::app()->getRequest()->getParam('section');
        if (!in_array($currentSection, $this->_buzziSections)) {
            return;
        }

        $message = '';

        $lastExecute = $this->_getLastExecuteCron();
        if (empty($lastExecute)) {
            $message = $this->_helper->__('There are no cron logs in magento database.');
        } elseif (strtotime($lastExecute) < time() - self::CRON_DELTA) {
            $message = $this->_helper->__('Cron has not been executed for more than %s', $this->_getDateDiff($lastExecute));
        }

        if (!empty($message)) {
            $this->_getAdminSession()->addError($message);
        }
    }

    /**
     * @return string
     */
    protected function _getLastExecuteCron()
    {
        $cronCollection = $this->_crateCronScheduleCollection();
        $cronCollection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(['last_execute' => 'max(executed_at)']);
        return $cronCollection->getFirstItem()->getLastExecute();
    }

    /**
     * @param string $dateTime
     * @return string
     */
    protected function _getDateDiff($dateTime)
    {
        $dateTime = new DateTime($dateTime);
        $diff  = $dateTime->diff(new DateTime('now'));
        return ($diff->days ? $diff->days . ' day(s)' : $diff->format('%H:%I') . ' hour(s)') . ' ago.';
    }

    /**
     * @return \Mage_Adminhtml_Model_Session
     */
    protected function _getAdminSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * @return \Mage_Cron_Model_Resource_Schedule_Collection
     */
    protected function _crateCronScheduleCollection()
    {
        return Mage::getResourceModel('cron/schedule_collection');
    }
}
