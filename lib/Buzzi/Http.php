<?php

namespace Buzzi;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LogLevel;
use Buzzi\Utils\StringUtils;
use Buzzi\Exception\HttpException;

class Http
{
    use StringUtils;

    const BUZZI_HEADER_PREFIX     = 'x-buzzi-';
    const BUZZI_VAR_HEADER_PREFIX = 'x-buzzi-var-';

    const DEFAULT_LOG_FILE_NAME = 'log/buzzi.log';
    const DEFAULT_LOG_LINE_FORMAT = "[%datetime%] %channel%.%level_name%: %message%\n";
    const DEFAULT_LOG_MESSAGE_FORMAT = "{method} - {uri}\nRequest body: {req_body}\nResponse: {code} {phrase}\nResponse body: {res_body}\n{error}\n";

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var \GuzzleHttp\HandlerStack
     */
    protected $handlerStack;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        $this->handlerStack = $this->createHandlerStack();
        $this->client = new Client([
            'base_uri' => $config[Sdk::CONFIG_HOST],
            'handler'  => $this->handlerStack,
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::AUTH => [$config[Sdk::CONFIG_AUTH_ID], $config[Sdk::CONFIG_AUTH_SECRET]]
        ]);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return \GuzzleHttp\HandlerStack
     */
    protected function createHandlerStack()
    {
        return HandlerStack::create();
    }

    /**
     * @param string|null $fileName
     * @param string|null $lineFormat
     * @param string|null $messageFormat
     * @return Http
     */
    public function addDefaultLogger($fileName = null, $lineFormat = null, $messageFormat = null)
    {
        $fileName = $fileName ?: static::DEFAULT_LOG_FILE_NAME;
        $lineFormat = $lineFormat ?: static::DEFAULT_LOG_LINE_FORMAT;
        $messageFormat = $messageFormat ?: static::DEFAULT_LOG_MESSAGE_FORMAT;

        $logHandler = new RotatingFileHandler($fileName);
        $logHandler->setFormatter(new LineFormatter($lineFormat, null, true));

        return $this->addLogger(new Logger('Logger', [$logHandler]), new MessageFormatter($messageFormat));
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \GuzzleHttp\MessageFormatter $messageFormatter
     * @return $this
     */
    public function addLogger($logger, $messageFormatter)
    {
        $this->handlerStack->push(
            $this->createMiddlewareLogCallback($logger, $messageFormatter),
            'logger'
        );
        return $this;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \GuzzleHttp\MessageFormatter $messageFormatter
     * @return callable
     */
    protected function createMiddlewareLogCallback($logger, $messageFormatter)
    {
        return Middleware::log($logger, $messageFormatter, LogLevel::DEBUG);
    }

    /**
     * @param string $uri
     * @param array|null $queryParams
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \RuntimeException
     */
    public function get($uri, array $queryParams = null)
    {
        return $this->client->get($uri, $this->buildRequestOptions($queryParams));
    }

    /**
     * @param int $batchCount
     * @param \Closure $onFulfilled
     * @param \Closure $onRejected
     * @param string $uri
     * @param array|null $queryParams
     * @return void
     */
    public function batchGet($batchCount, $onFulfilled, $onRejected, $uri, array $queryParams = null)
    {
        $options = $this->buildRequestOptions($queryParams);

        $requests = function ($count) use ($uri, $options) {
            for ($i = 0; $i < $count; $i++) {
                yield function() use ($uri, $options) {
                    return $this->client->getAsync($uri, $options);
                };
            }
        };

        $pool = new Pool(
            $this->client,
            $requests($batchCount),
            [
                'fulfilled' => $onFulfilled,
                'rejected' => $onRejected,
            ]
        );

        $pool->promise()->wait();
    }

    /**
     * @param string $uri
     * @param array|null $postData
     * @param array|null $queryParams
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \RuntimeException
     */
    public function post($uri, array $postData = null, array $queryParams = null)
    {
        return $this->client->post($uri, $this->buildRequestOptions($queryParams, $postData));
    }

    /**
     * @param string $uri
     * @param array $multipart
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \RuntimeException
     */
    public function upload($uri, array $multipart)
    {
        return $this->client->post($uri, [RequestOptions::MULTIPART => $multipart]);
    }

    /**
     * @param string $uri
     * @param array|null $queryParams
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \RuntimeException
     */
    public function delete($uri, array $queryParams = null)
    {
        return $this->client->delete($uri, $this->buildRequestOptions($queryParams));
    }

    /**
     * @param array|null $queryParams
     * @param array|null $postData
     * @return array
     */
    protected function buildRequestOptions(array $queryParams = null, array $postData = null)
    {
        $options = [];
        if (!empty($queryParams)) {
            $options[RequestOptions::QUERY] = $queryParams;
        }
        if (!empty($postData)) {
            $options[RequestOptions::JSON] = (object)$postData;
        }
        return $options;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return array
     */
    public function parseHeaders($response)
    {
        $data = [];
        $variables = [];

        foreach ($response->getHeaders() as $name => $values) {
            if (strpos($name, self::BUZZI_HEADER_PREFIX) === false) {
                continue;
            }

            if (strpos($name, self::BUZZI_VAR_HEADER_PREFIX) === false) {
                $data[$this->kebabCaseToSnakeCase(str_replace(self::BUZZI_HEADER_PREFIX, '', $name))] = implode(', ', $values);
            } else {
                $variables[str_replace(self::BUZZI_VAR_HEADER_PREFIX, '', $name)] = implode(', ', $values);
            }
        }

        $data['variables'] = $variables;
        return $data;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return array|null
     * @throws \Buzzi\Exception\HttpException
     */
    public function readResponse($response)
    {
        if ($response->getStatusCode() >= 300) {
            throw new HttpException($response);
        }

        $body = (string)$response->getBody();
        return !empty($body) ? json_decode($body, true) : null;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param int $expectedStatusCode
     * @return bool
     * @throws \Buzzi\Exception\HttpException
     */
    public function validateResponse($response, $expectedStatusCode = 200)
    {
        if ($response->getStatusCode() != $expectedStatusCode) {
            throw new HttpException($response);
        }
        return true;
    }
}
