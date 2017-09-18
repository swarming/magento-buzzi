<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Model_Platform
{
    /**
     * @var \Buzzi_Base_Model_Platform_Registry
     */
    protected $_platformRegistry;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_platformRegistry = Mage::getSingleton('buzzi_base/platform_registry');
    }

    /**
     * @param int|null $storeId
     * @return \Buzzi\Publish\PublishService
     */
    protected function _getPublishService($storeId)
    {
        return $this->_platformRegistry->getSdk($storeId)->getPublishService();
    }

    /**
     * @param string $eventType
     * @param mixed[] $payload
     * @param int $storeId
     * @return string
     */
    public function send($eventType, $payload, $storeId)
    {
        return $this->_getPublishService($storeId)->send($eventType, (array)$payload);
    }

    /**
     * @param array $multipart
     * @param int|null $storeId
     * @return void
     */
    public function upload($multipart, $storeId)
    {
        $this->_getPublishService($storeId)->upload($multipart);
    }
}
