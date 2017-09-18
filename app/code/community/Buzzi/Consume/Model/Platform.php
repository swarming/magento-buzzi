<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Model_Platform
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
     * @return \Buzzi\Consume\ConsumeService
     */
    protected function _getConsumeService($storeId)
    {
        return $this->_platformRegistry->getSdk($storeId)->getConsumeService();
    }

    /**
     * @param int $maxQty
     * @param int $storeId
     * @return \Buzzi\Consume\Delivery[]
     */
    public function fetch($maxQty = 0, $storeId = null)
    {
        list($deliveries, $exceptions) = $this->_getConsumeService($storeId)->batchFetch($maxQty);
        $this->_logExceptions($exceptions);
        return $deliveries;
    }

    /**
     * @param \Exception[] $exceptions
     * @return void
     */
    protected function _logExceptions($exceptions)
    {
        foreach ($exceptions as $exception) {
            Mage::logException($exception);
        }
    }

    /**
     * @param string $receipt
     * @param int $storeId
     * @return bool
     */
    public function confirm($receipt, $storeId)
    {
        try {
            $result = $this->_getConsumeService($storeId)->confirm($receipt);
        } catch (Exception $e) {
            $result = false;
            Mage::logException($e);
        }

        return $result;
    }

    /**
     * @param string $receipt
     * @param array $errorData
     * @param int $storeId
     * @return bool
     */
    public function submitError($receipt, array $errorData, $storeId)
    {
        try {
            $result = $this->_getConsumeService($storeId)->submitError($receipt, $errorData);
        } catch (Exception $e) {
            $result = false;
            Mage::logException($e);
        }

        return $result;
    }
}
