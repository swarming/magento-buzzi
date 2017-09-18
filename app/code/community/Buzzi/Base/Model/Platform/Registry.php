<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Base_Model_Platform_Registry
{
    /**
     * @var \Buzzi_Base_Model_Platform_SdkFactory
     */
    protected $sdkFactory;

    /**
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * @var \Buzzi\Sdk[]
     */
    protected $register = [];

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->sdkFactory = Mage::getModel('buzzi_base/platform_sdkFactory');
        $this->_app = Mage::app();
    }

    /**
     * @param int|string|null $storeId
     * @return \Buzzi\Sdk
     */
    public function getSdk($storeId = null)
    {
        $storeId = $this->_app->getStore($storeId)->getId();

        if (empty($this->register[$storeId])) {
            $this->register[$storeId] = $this->sdkFactory->create([Buzzi_Base_Model_Platform_SdkFactory::CONFIG_STORE => $storeId]);
        }

        return $this->register[$storeId];
    }
}
