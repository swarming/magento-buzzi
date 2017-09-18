<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Base_Model_Config_General
{
    const XML_PATH_ENABLED = 'buzzi_base/general/enabled';

    /**
     * @var \Buzzi_Base_Model_Config_System_Context
     */
    protected $_configContext;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configContext = Mage::getSingleton('buzzi_base/config_system_context');
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED, $this->_getStore($storeId));
    }

    /**
     * @param string|int|null $storeId
     * @return string|int|null
     */
    protected function _getStore($storeId = null)
    {
        return $storeId ?: $this->_configContext->getCurrentStore();
    }
}
