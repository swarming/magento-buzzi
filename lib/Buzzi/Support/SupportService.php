<?php

namespace Buzzi\Support;

class SupportService
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
     * @return bool
     * @throws \Buzzi\Exception\HttpException
     */
    public function ping()
    {
        $response = $this->http->get('/ping');
        return $this->http->validateResponse($response);
    }

    /**
     * Checks whether credentials are valid
     * If credentials are not valid throws HttpException exception
     *
     * @return bool
     * @throws \Buzzi\Exception\HttpException
     */
    public function isAuthorized()
    {
        $response = $this->http->get('/authorized');
        return $this->http->validateResponse($response);
    }
}
