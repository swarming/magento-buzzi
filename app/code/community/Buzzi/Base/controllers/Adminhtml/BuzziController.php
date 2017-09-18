<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
use Buzzi\Sdk;

class Buzzi_Base_Adminhtml_BuzziController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var \Buzzi_Base_Model_Config_Api
     */
    protected $_configApi;

    /**
     * @var \Buzzi_Base_Model_Platform_SdkFactory
     */
    protected $_sdkFactory;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_configApi = Mage::getModel('buzzi_base/config_api');
        $this->_sdkFactory = Mage::getModel('buzzi_base/platform_sdkFactory');
        parent::_construct();
    }

    /**
     * @return void
     */
    public function testConnectionAction()
    {
        $response = [
            'status' => 'fail',
            'message' => $this->__('Invalid credentials.')
        ];

        $environment = $this->getRequest()->getParam('environment');
        $host = $this->getRequest()->getParam('host');
        $authId = $this->getRequest()->getParam('auth_id');
        $authSecret = $this->getRequest()->getParam('auth_secret');

        if (!empty($environment) && !empty($authId) && !empty($authSecret)) {
            try {
                $sdk = $this->_createSdk($environment, $host, $authId, $authSecret);
                $sdk->getSupportService()->isAuthorized();
                $response = [
                    'status' => 'success',
                    'message' => $this->__('Connected Successfully.')
                ];
            } catch (\Buzzi\Exception\HttpException $e) {
                $response['message'] = $e->getMessage();
            }
        }

        $this->_sendAjaxResponse($response);
    }

    /**
     * @param string $environment
     * @param string $host
     * @param string $authId
     * @param string $authSecret
     * @return \Buzzi\Sdk
     */
    protected function _createSdk($environment, $host, $authId, $authSecret)
    {
        $sdk = $this->_sdkFactory->create([
            Buzzi_Base_Model_Platform_SdkFactory::CONFIG_ENVIRONMENT => $environment,
            Sdk::CONFIG_HOST => $host,
            Sdk::CONFIG_AUTH_ID => $authId,
            Sdk::CONFIG_AUTH_SECRET => $this->_updateEncryptedClientSecret($authSecret, $environment),
        ]);
        return $sdk;
    }

    /**
     * @param string $authSecret
     * @param string $environment
     * @return string
     */
    protected function _updateEncryptedClientSecret($authSecret, $environment)
    {
        return $authSecret == '******' ? $this->_configApi->getAuthSecret($environment) : $authSecret;
    }

    /**
     * @param array $response
     * @return void
     */
    protected function _sendAjaxResponse(array $response)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode((object)$response));
    }
}
