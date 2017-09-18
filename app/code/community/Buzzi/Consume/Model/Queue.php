<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Consume_Model_Queue
{
    /**
     * @var \Buzzi_Consume_Model_Config_General
     */
    protected $_configGeneral;

    /**
     * @var \Buzzi_Consume_Model_Config_Events
     */
    protected $_configEvents;

    /**
     * @var \Buzzi_Consume_Model_HandlerRegistry
     */
    protected $_handlerRegistry;

    /**
     * @var \Buzzi_Consume_Model_Platform
     */
    protected $_platform;

    /**
     * @var \Buzzi_Consume_Model_Delivery_PayloadPacker
     */
    protected $_payloadPacker;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configGeneral = Mage::getModel('buzzi_consume/config_general');
        $this->_configEvents = Mage::getSingleton('buzzi_consume/config_events');
        $this->_handlerRegistry = Mage::getModel('buzzi_consume/handlerRegistry');
        $this->_platform = Mage::getModel('buzzi_consume/platform');
        $this->_payloadPacker = Mage::getModel('buzzi_consume/delivery_payloadPacker');
    }

    /**
     * @return \Buzzi_Consume_Model_Delivery
     */
    protected function _createDeliveryModel()
    {
        return Mage::getModel('buzzi_consume/delivery');
    }

    /**
     * @return \Buzzi_Consume_Model_Resource_Delivery_Collection
     */
    protected function _createDeliveryCollection()
    {
        return Mage::getResourceModel('buzzi_consume/delivery_collection');
    }

    /**
     * @param \Buzzi\Consume\Delivery[] $platformDeliveries
     * @param int $storeId
     * @return void
     */
    public function addDeliveries($platformDeliveries, $storeId)
    {
        foreach ($platformDeliveries as $platformDelivery) {
            if (!$this->_canSaveDelivery($platformDelivery->getEventType(), $storeId)) {
                continue;
            }

            $delivery = $this->_createDeliveryModel();
            $delivery->setStoreId($storeId);
            $delivery->setEventType($platformDelivery->getEventType());
            $this->_payloadPacker->packPayload($delivery, (array)$platformDelivery->getBody());
            $delivery->setReceipt($platformDelivery->getReceipt());
            $delivery->setStatus(Buzzi_Consume_Model_Delivery::STATUS_PENDING);
            $delivery->save();
        }
    }

    /**
     * @param string|null $eventType
     * @param int $storeId
     * @return bool
     */
    protected function _canSaveDelivery($eventType, $storeId)
    {
        $fetchAll = $this->_configGeneral->getFetchType($storeId) == Buzzi_Consume_Model_Config_System_Source_FetchType::ALL;
        $fetchRegistered = $this->_configGeneral->getFetchType($storeId) == Buzzi_Consume_Model_Config_System_Source_FetchType::REGISTERED;
        $fetchEnabled = $this->_configGeneral->getFetchType($storeId) == Buzzi_Consume_Model_Config_System_Source_FetchType::ENABLED;

        return $fetchAll
            || ($fetchRegistered && in_array($fetchRegistered, $this->_configEvents->getAllTypes()))
            || ($fetchEnabled && $this->_configEvents->isEventEnabled($eventType, $storeId));
    }

    /**
     * @param string $eventType
     * @param int|null $storeId
     * @return int
     */
    public function handleByType($eventType, $storeId = null)
    {
        $deliveries = $this->_createDeliveryCollection();
        $deliveries->filterType($eventType);
        $deliveries->filterPending();
        if ($storeId) {
            $deliveries->filterStore($storeId);
        }

        return $this->_handleDeliveries($deliveries);
    }

    /**
     * @param int[] $deliveryIds
     * @return int
     */
    public function handleByIds(array $deliveryIds)
    {
        $deliveries = $this->_createDeliveryCollection();
        if ($deliveryIds) {
            $deliveries->filterDeliveryIds($deliveryIds);
        }

        return $this->_handleDeliveries($deliveries);
    }

    /**
     * @param Buzzi_Consume_Model_Resource_Delivery_Collection $deliveries
     * @return int
     */
    protected function _handleDeliveries($deliveries)
    {
        $counter = 0;
        foreach ($deliveries as $delivery) {
            try {
                $counter += $this->_handle($delivery) ? 1 : 0;
            } catch (\Exception $e) {
                Mage::logException($e);
            }
        }
        return $counter;
    }

    /**
     * @param \Buzzi_Consume_Model_Delivery $delivery
     * @return bool
     */
    protected function _handle($delivery)
    {
        if (Buzzi_Consume_Model_Delivery::STATUS_DONE == $delivery->getStatus()) {
            Mage::throwException(sprintf('Delivery with %s ID is already processed.', $delivery->getDeliveryId()));
        }

        $isProcessed = false;
        $isConfirmed = false;
        $errorMessage = null;
        $errorData = [];

        try {
            $handler = $this->_handlerRegistry->getHandler($delivery->getEventType());
            $isProcessed = $handler->handle($delivery);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $errorData = [
                'message' => $errorMessage,
                'trace' => $e->getTraceAsString()
            ];
        }

        if ($isProcessed) {
            $isConfirmed = $this->_platform->confirm($delivery->getReceipt(), $delivery->getStoreId());
        } else {
            $this->_platform->submitError($delivery->getReceipt(), $errorData, $delivery->getStoreId());
        }

        if ($isProcessed && $this->_configGeneral->isRemoveImmediately($delivery->getStoreId())) {
            $this->_deleteDelivery($delivery);
        } else {
            $this->_updateDelivery($delivery, $isProcessed, $isConfirmed, $errorMessage);
        }

        return $isProcessed;
    }

    /**
     * @param \Buzzi_Consume_Model_Delivery $delivery
     * @param bool $isProcessed
     * @param bool $isConfirmed
     * @param string|null $errorMessage
     * @return void
     */
    protected function _updateDelivery($delivery, $isProcessed, $isConfirmed, $errorMessage)
    {
        if ($isProcessed) {
            $delivery->setIsConfirmed($isConfirmed);
            $delivery->setStatus(Buzzi_Consume_Model_Delivery::STATUS_DONE);
            $delivery->setErrorMessage('');
        } else {
            $delivery->setStatus(Buzzi_Consume_Model_Delivery::STATUS_FAIL);
            $delivery->setErrorMessage($errorMessage);
        }

        $count = $delivery->getCounter();
        $delivery->setCounter(++$count);

        $delivery->save();
    }

    /**
     * @param int $delay
     * @param int|null $storeId
     * @return void
     */
    public function deleteDone($delay, $storeId = null)
    {
        $deliveries = $this->_createDeliveryCollection();
        $deliveries->filterDone();
        $deliveries->filterHandleTime($delay);
        if ($storeId) {
            $deliveries->filterStore($storeId);
        }

        /** @var \Buzzi_Consume_Model_Delivery $delivery */
        foreach ($deliveries as $delivery) {
            $this->_deleteDelivery($delivery);
        };
    }

    /**
     * @param int[] $deliveryIds
     * @return void
     */
    public function deleteByIds(array $deliveryIds)
    {
        $deliveries = $this->_createDeliveryCollection();
        if ($deliveryIds) {
            $deliveries->filterDeliveryIds($deliveryIds);
        }

        /** @var \Buzzi_Consume_Model_Delivery $delivery */
        foreach ($deliveries as $delivery) {
            $this->_deleteDelivery($delivery);
        };
    }

    /**
     * @param \Buzzi_Consume_Model_Delivery $delivery
     * @return void
     */
    protected function _deleteDelivery($delivery)
    {
        $this->_payloadPacker->cleanPayload($delivery);
        $delivery->delete();
    }
}
