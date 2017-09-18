<?php

namespace Buzzi\Exception;

use Exception;
use Psr\Http\Message\ResponseInterface;

class HttpException extends \RuntimeException
{
    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(ResponseInterface $response, $code = 0, Exception $previous = null)
    {
        $this->response = $response;

        parent::__construct($this->getErrorMessage(), $code, $previous);
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * @param bool $decoded
     * @return array|null|string
     */
    public function getResponseBody($decoded = true)
    {
        $responseBody = (string)$this->response->getBody();
        return $decoded ? json_decode($responseBody, true) : $responseBody;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return string
     */
    protected function getErrorMessage()
    {
        $responseBody = $this->getResponseBody();
        return !empty($responseBody['message']) ? $responseBody['message'] : $this->response->getReasonPhrase();
    }
}
