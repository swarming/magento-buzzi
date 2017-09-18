<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
use Buzzi\Sdk;

class Buzzi_Base_Model_Platform_SdkFactory
{
    const CONFIG_STORE = 'store';
    const CONFIG_ENVIRONMENT = 'environment';

    /**
     * @var string
     */
    protected static $_debugFile = '/log/buzzi/buzzi.log';

    /**
     * @var \Buzzi_Base_Model_Config_Api
     */
    protected $configApi;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->configApi = Mage::getModel('buzzi_base/config_api');
    }

    /**
     * @param string[] $config
     * @return \Buzzi\Sdk
     */
    public function create(array $config = [])
    {
        $store = !empty($config[self::CONFIG_STORE]) ? $config[self::CONFIG_STORE] : null;
        unset($config[self::CONFIG_STORE]);

        $environment = !empty($config[self::CONFIG_ENVIRONMENT]) ? $config[self::CONFIG_ENVIRONMENT] : $this->configApi->getEnvironment($store);
        $isSandbox = $environment == Buzzi_Base_Model_Config_System_Source_Environment::SANDBOX;
        unset($config[self::CONFIG_ENVIRONMENT]);

        $sdkConfig = array_merge([
            Sdk::CONFIG_HOST => $this->configApi->getHost($store),
            Sdk::CONFIG_AUTH_ID => $this->configApi->getAuthId($store),
            Sdk::CONFIG_AUTH_SECRET => $this->configApi->getAuthSecret(null, $store),
            Sdk::CONFIG_SANDBOX => $isSandbox,
            Sdk::CONFIG_DEBUG => $this->configApi->isDebug($store),
            Sdk::CONFIG_LOG_FILE_NAME => Mage::getBaseDir('var') . self::$_debugFile
        ], $config);

        return new Sdk($sdkConfig);
    }
}
