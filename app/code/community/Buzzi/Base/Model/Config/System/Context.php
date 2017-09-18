<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Base_Model_Config_System_Context
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
     * @var string|null
     */
    protected $_currentStore;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_app = Mage::app();
        $this->_request = $this->_app->getRequest();
    }

    /**
     * @return string|null
     */
    public function getCurrentStore()
    {
        if (null === $this->_currentStore) {
            $this->_initCurrentStore();
        }
        return $this->_currentStore;
    }

    /**
     * @return $this
     */
    protected function _initCurrentStore()
    {
        if (!$this->_app->getStore()->isAdmin()) {
            $this->_currentStore = $this->_app->getStore()->getCode();
        } elseif ($this->_request->getParam('website')) {
            $website = $this->_app->getWebsite($this->_request->getParam('website'));
            $this->_currentStore = $website->getDefaultStore()->getCode();
        } elseif ($this->_request->getParam('store')) {
            $this->_currentStore = $this->_app->getStore($this->_request->getParam('store'));
        }
        return $this;
    }
}
