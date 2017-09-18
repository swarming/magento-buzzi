<?php

namespace Buzzi\Consume;

use Buzzi\Exception\HttpException;

class ConsumeService
{
    /**
     * @var \Buzzi\Http
     */
    protected $http;

    /**
     * @param \Buzzi\Http $http
     */
    public function __construct(
        \Buzzi\Http $http
    ) {
        $this->http = $http;
    }

    /**
     * Fetches one delivery event from Buzzi platform
     * If there is not events on Buzzi platform returns null
     *
     * @return \Buzzi\Consume\Delivery|null
     * @throws \Buzzi\Exception\HttpException
     */
    public function fetch()
    {
        $response = $this->http->get('/event');
        return $this->readDelivery($response);
    }

    /**
     * Fetches all delivery events from Buzzi platform
     * If pass $noMoreThan value, then fetches all delivery events but not more than specified number
     * If any request is not successful the exception list contains an exception
     *
     * @param int $notMoreThan
     * @return array [\Buzzi\Consume\Delivery[], \Exception[]]
     */
    public function batchFetch($notMoreThan = 0)
    {
        $deliveries = [];
        $exceptions = [];

        $batchCount = $this->calculateBatchCount($notMoreThan);
        $this->http->batchGet(
            $batchCount,
            function ($response, $index) use (&$deliveries, &$exceptions) {
                try {
                    $deliveries[$index] = $this->readDelivery($response);
                } catch (\Exception $e) {
                    $exceptions[$index] = $e;
                }
            },
            function ($reason, $index) use (&$exceptions) {
                $exceptions[$index] = new HttpException($reason);
            },
            '/event'
        );

        return [$deliveries, $exceptions];
    }

    /**
     * @param int $notMoreThan
     * @return int
     */
    protected function calculateBatchCount($notMoreThan)
    {
        if (!is_numeric($notMoreThan) || $notMoreThan < 0) {
            throw new \InvalidArgumentException('$notMoreThan must be a number grater then 0.');
        }

        $count = $this->getCount();
        return $notMoreThan ? min($count, $notMoreThan) : $count;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Buzzi\Consume\Delivery
     * @throws \Buzzi\Exception\HttpException
     */
    protected function readDelivery($response)
    {
        return $response->getStatusCode() !== 204 ? $this->createDeliveryFromResponse($response) : null;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Buzzi\Consume\Delivery
     * @throws \Buzzi\Exception\HttpException
     */
    protected function createDeliveryFromResponse($response)
    {
        $deliveryData = $this->http->parseHeaders($response);
        $deliveryData['body'] = $this->http->readResponse($response);
        return new Delivery($deliveryData);
    }

    /**
     * Returns a number of delivery events in queue on Buzzi platform
     *
     * @return int
     * @throws \RuntimeException
     * @throws \Buzzi\Exception\HttpException
     */
    public function getCount()
    {
        $response = $this->http->get('/event/_count');

        $result = $this->http->readResponse($response);
        if (!isset($result['count'])) {
            throw new \RuntimeException('Result does not contain "count" value.');
        }

        return (int)$result['count'];
    }

    /**
     * Confirm that delivery event is successfully processed
     *
     * @param \Buzzi\Consume\Delivery $delivery
     * @return bool
     * @throws \Buzzi\Exception\HttpException
     */
    public function confirmDelivery(Delivery $delivery)
    {
        return $this->confirm($delivery->getReceipt());
    }

    /**
     * Confirm that delivery event is successfully processed
     *
     * @param string $deliveryReceipt
     * @return bool
     * @throws \Buzzi\Exception\HttpException
     */
    public function confirm($deliveryReceipt)
    {
        $response = $this->http->post('/delivery/confirm', null, ['receipt' => $deliveryReceipt]);
        return $this->http->validateResponse($response);
    }

    /**
     * @param string $deliveryReceipt
     * @param mixed[] $errorData
     * @return bool
     * @throws \Buzzi\Exception\HttpException
     */
    public function submitError($deliveryReceipt, array $errorData)
    {
        $response = $this->http->post('/delivery/error', $errorData, ['receipt' => $deliveryReceipt]);
        return $this->http->validateResponse($response);
    }
}
