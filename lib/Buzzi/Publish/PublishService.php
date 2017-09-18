<?php

namespace Buzzi\Publish;

use Buzzi\Sdk;

class PublishService
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
     * Publishes a submission event data to Buzzi platform and returns event Id
     *
     * @param string $eventType
     * @param array $payload
     * @param string $version
     * @return string
     * @throws \RuntimeException
     * @throws \Buzzi\Exception\HttpException
     */
    public function send($eventType, array $payload, $version = Sdk::API_DEFAULT_VERSION)
    {
        $response = $this->http->post(sprintf('/event/%s/%s', $eventType, $version), $payload);

        $result = $this->http->readResponse($response);
        if (!isset($result['event'])) {
            throw new \RuntimeException('Result does not contain "event" value.');
        }
        return $result['event'];
    }

    /**
     * Upload File(s)
     *
     * $multipart - accepts an array of associative arrays, where each associative array contains the following keys:
     * - name: (required, string) key mapping to the form field name.
     * - contents: (required, mixed) Provide a string to send the contents of the file as a string,
     *   provide an fopen resource to stream the contents from a PHP stream, or
     *   provide a Psr\Http\Message\StreamInterface to stream the contents from a PSR-7 stream.
     * @see http://docs.guzzlephp.org/en/stable/quickstart.html#sending-form-files
     *
     * @param array $multipart
     * @return bool
     * @throws \Buzzi\Exception\HttpException
     */
    public function upload(array $multipart)
    {
        $response =  $this->http->upload('/files', $multipart);
        return $this->http->validateResponse($response);
    }
}
