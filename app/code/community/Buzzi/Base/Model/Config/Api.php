<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Base_Model_Config_Api extends Buzzi_Base_Model_Config_General
{
    /**
     * @param int|null $storeId
     * @return string
     */
    public function getEnvironment($storeId = null)
    {
        return Mage::getStoreConfig('buzzi_base/api/environment', $this->_getStore($storeId));
    }

    /**
     * @param int|null $storeId
     * @return string|null
     */
    public function getHost($storeId = null)
    {
        $storeId = $this->_getStore($storeId);
        return $this->getEnvironment($storeId) == Buzzi_Base_Model_Config_System_Source_Environment::CUSTOM
            ? Mage::getStoreConfig('buzzi_base/api/custom_host', $storeId)
            : null;
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getAuthId($storeId = null)
    {
        $storeId = $this->_getStore($storeId);
        return Mage::getStoreConfig('buzzi_base/api/' . $this->getEnvironment($storeId) . '_id', $storeId);
    }

    /**
     * @param string|null $environment
     * @param int|null $storeId
     * @return string
     */
    public function getAuthSecret($environment = null, $storeId = null)
    {
        $storeId = $this->_getStore($storeId);
        $environment = $environment ?: $this->getEnvironment($storeId);
        return Mage::getStoreConfig('buzzi_base/api/' . $environment . '_secret', $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isDebug($storeId = null)
    {
        return Mage::getStoreConfigFlag('buzzi_base/api/debug', $this->_getStore($storeId));
    }
}
