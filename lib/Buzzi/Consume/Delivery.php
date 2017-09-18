<?php

namespace Buzzi\Consume;

use Buzzi\Utils\StringUtils;

/**
 * @method string getAccountId()
 * @method string getAccountDisplay()
 * @method string getConsumerId()
 * @method string getConsumerDisplay()
 * @method string getDeliveryId()
 * @method string getEventId()
 * @method string getEventType()
 * @method string getEventVersion()
 * @method string getEventDisplay()
 * @method string getProducerId()
 * @method string getProducerDisplay()
 * @method string getIntegrationId
 * @method string getIntegrationDisplay
 * @method string getReceipt
 * @method array getVariables
 * @method array getBody
 */
class Delivery
{
    use StringUtils;

    /**
     * @var
     */
    protected $data = [];

    /**
     * @param array $data A key-value pair: key: property name && value: property value.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                $key = $this->underscore(substr($method, 3));
                return $this->getData($key);
        }

        throw new \BadMethodCallException(self::class . '::' . $method . ' method is not defined.');
    }

    /**
     * @param $key
     * @return mixed|null
     */
    protected function getData($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }
}
