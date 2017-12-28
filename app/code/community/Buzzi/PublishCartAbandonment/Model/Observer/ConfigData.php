<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_PublishCartAbandonment_Model_Observer_ConfigData
{
    /**
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * @var Mage_Core_Controller_Request_Http
     */
    protected $_request;

    /**
     * @var \Buzzi_Publish_Model_Config_Events
     */
    protected $_configEvents;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_app = Mage::app();
        $this->_request = Mage::app()->getRequest();
        $this->_configEvents = Mage::getSingleton('buzzi_publish/config_events');
    }

    /**
     * @return \Mage_Adminhtml_Model_Session
     */
    protected function _getAdminhtmlSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * @return \Mage_Core_Model_Config
     */
    protected function _createConfigModel()
    {
        return Mage::getModel('core/config');
    }

    /**
     * @return bool
     */
    protected function isCheckNeeded()
    {
        return defined('Mage_Log_Helper_Data::XML_PATH_LOG_ENABLED')
            && defined('Mage_Log_Model_Adminhtml_System_Config_Source_Loglevel::LOG_LEVEL_ALL');
    }

    /**
     * @param \Varien_Event_Observer $observer
     * @return void
     */
    public function execute($observer)
    {
        if (!$this->isCheckNeeded()) {
            return;
        }

        /** @var \Mage_Adminhtml_Model_Config_Data $configData */
        $configData = $observer->getData('object');
        switch ($configData->getSection()) {
            case 'system':
                $this->_onSystemSave($configData);
                break;
            case 'buzzi_base':
                $this->_onCartAbandonmentEnable($configData);
                break;
            case 'buzzi_publish_events':
                $this->_onCartAbandonmentUpdate($configData);
                break;
        }
    }

    /**
     * @param \Mage_Adminhtml_Model_Config_Data $configData
     * @return void
     */
    protected function _onCartAbandonmentEnable($configData)
    {
        $groups = $configData->getData('groups');
        if (isset($groups['publish']['fields']['events']['value']) && is_array($groups['publish']['fields']['events']['value'])
            && in_array(Buzzi_PublishCartAbandonment_Model_DataBuilder::EVENT_TYPE, $groups['publish']['fields']['events']['value'])
            && $this->_configEvents->getValue(Buzzi_PublishCartAbandonment_Model_DataBuilder::EVENT_TYPE, 'track_online_customers', $this->_getStoreId())
            && Mage::getStoreConfig(Mage_Log_Helper_Data::XML_PATH_LOG_ENABLED) != Mage_Log_Model_Adminhtml_System_Config_Source_Loglevel::LOG_LEVEL_ALL
        ) {
            $this->_createConfigModel()->saveConfig(Mage_Log_Helper_Data::XML_PATH_LOG_ENABLED, Mage_Log_Model_Adminhtml_System_Config_Source_Loglevel::LOG_LEVEL_ALL);
            $this->_getAdminhtmlSession()->addWarning('Buzzi Cart Abandonment Publish Event Depends on System -> Log -> Enable Log = Yes. The value was set.');
        }
    }

    /**
     * @param \Mage_Adminhtml_Model_Config_Data $configData
     * @return void
     */
    protected function _onCartAbandonmentUpdate($configData)
    {
        $groups = $configData->getData('groups');
        if (isset($groups['cart_abandonment']['fields']['track_online_customers']['value'])
            && $groups['cart_abandonment']['fields']['track_online_customers']['value'] == 1
            && $this->_configEvents->isEventEnabled(Buzzi_PublishCartAbandonment_Model_DataBuilder::EVENT_TYPE, $this->_getStoreId())
            && Mage::getStoreConfig(Mage_Log_Helper_Data::XML_PATH_LOG_ENABLED) != Mage_Log_Model_Adminhtml_System_Config_Source_Loglevel::LOG_LEVEL_ALL
        ) {
            $this->_createConfigModel()->saveConfig(Mage_Log_Helper_Data::XML_PATH_LOG_ENABLED, Mage_Log_Model_Adminhtml_System_Config_Source_Loglevel::LOG_LEVEL_ALL);
            $this->_getAdminhtmlSession()->addWarning('Buzzi Cart Abandonment Publish Event Depends on System -> Log -> Enable Log = Yes. The value was set.');
        }
    }

    /**
     * @param \Mage_Adminhtml_Model_Config_Data $configData
     * @return void
     */
    protected function _onSystemSave($configData)
    {
        $groups = $configData->getData('groups');
        if (isset($groups['log']['fields']['enable_log']['value'])
            && $groups['log']['fields']['enable_log']['value'] != 1
            && $this->_isCartAbandonmentEnabled()
        ) {
            $groups['log']['fields']['enable_log']['value'] = 1;
            $configData->setData('groups', $groups);
            $this->_getAdminhtmlSession()->addWarning('Buzzi Cart Abandonment Publish Event Depends on System -> Log -> Enable Log = Yes.');
        }
    }

    /**
     * Check in all stores
     *
     * @return bool
     */
    protected function _isCartAbandonmentEnabled()
    {
        foreach ($this->_app->getStores() as $store) {
            if ($this->_configEvents->isEventEnabled(Buzzi_PublishCartAbandonment_Model_DataBuilder::EVENT_TYPE, $store)
                && $this->_configEvents->getValue(Buzzi_PublishCartAbandonment_Model_DataBuilder::EVENT_TYPE, 'track_online_customers', $store)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return int|null
     */
    protected function _getStoreId()
    {
        $websiteCode = $this->_request->getParam('website');
        if ($websiteCode && $this->_app->getWebsite($websiteCode) && $this->_app->getWebsite($websiteCode)->getDefaultStore()) {
            return $this->_app->getWebsite($websiteCode)->getDefaultStore()->getId();
        }
        return null;
    }
}
