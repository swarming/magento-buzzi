<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */
class Buzzi_Consume_Model_HandlerRegistry
{
    /**
     * @var \Buzzi_Consume_Model_Config_Events
     */
    protected $_configEvents;

    /**
     * @var \Buzzi_Consume_Model_HandlerInterface[]
     */
    protected $_handlers = [];

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_configEvents = Mage::getSingleton('buzzi_consume/config_events');
    }

    /**
     * @param string $eventType
     * @return \Buzzi_Consume_Model_HandlerInterface
     */
    public function getHandler($eventType)
    {
        $handlerClass = $this->_configEvents->getHandler($eventType);
        if (empty($handlerClass)) {
            throw new \DomainException(sprintf('Event handler is not found for "%s" event type.', $eventType));
        }

        if (empty($this->_handlers[$handlerClass])) {
            $this->_handlers[$handlerClass] = $this->_createHandler($handlerClass);
        }
        return $this->_handlers[$handlerClass];
    }

    /**
     * @param string $handlerClass
     * @return \Buzzi_Consume_Model_HandlerInterface
     */
    protected function _createHandler($handlerClass)
    {
        $handler = Mage::getModel($handlerClass);

        if (!$handler instanceof Buzzi_Consume_Model_HandlerInterface) {
            throw new \InvalidArgumentException(get_class($handler) . ' must be an instance of Buzzi_Consume_Model_HandlerInterface.');
        }

        return $handler;
    }
}
