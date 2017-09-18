<?php

namespace Buzzi;

use Buzzi\Support\SupportService;
use Buzzi\Publish\PublishService;
use Buzzi\Consume\ConsumeService;

class Sdk
{
    const API_DEFAULT_VERSION = 'v1.0';
    const API_DEFAULT_HOST    = 'https://core.buzzi.io';
    const API_SANDBOX_HOST    = 'https://sandbox-core.buzzi.io';
    const API_HOST_ENV_NAME   = 'BUZZI_API_HOST';
    const API_ID_ENV_NAME     = 'BUZZI_API_ID';
    const API_SECRET_ENV_NAME = 'BUZZI_API_SECRET';

    const CONFIG_HOST               = 'host';
    const CONFIG_AUTH_ID            = 'auth_id';
    const CONFIG_AUTH_SECRET        = 'auth_secret';
    const CONFIG_SANDBOX            = 'sandbox';
    const CONFIG_DEBUG              = 'debug';
    const CONFIG_LOG_FILE_NAME      = 'log_file_name';
    const CONFIG_LOG_LINE_FORMAT    = 'log_line_format';
    const CONFIG_LOG_MESSAGE_FORMAT = 'log_message_format';

    /**
     * @var \Buzzi\Http
     */
    protected $http;

    /**
     * @var \Buzzi\Support\SupportService
     */
    protected $support;

    /**
     * @var \Buzzi\Publish\PublishService
     */
    protected $publish;

    /**
     * @var \Buzzi\Consume\ConsumeService
     */
    protected $consume;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $config = array_merge(
            [
                self::CONFIG_HOST               => getenv(self::API_HOST_ENV_NAME),
                self::CONFIG_AUTH_ID            => getenv(self::API_ID_ENV_NAME),
                self::CONFIG_AUTH_SECRET        => getenv(self::API_SECRET_ENV_NAME),
                self::CONFIG_SANDBOX            => false,
                self::CONFIG_DEBUG              => false,
                self::CONFIG_LOG_FILE_NAME      => null,
                self::CONFIG_LOG_LINE_FORMAT    => null,
                self::CONFIG_LOG_MESSAGE_FORMAT => null
            ],
            $config
        );

        if (empty($config[self::CONFIG_HOST])) {
            $config[self::CONFIG_HOST] = $config[self::CONFIG_SANDBOX] ? self::API_SANDBOX_HOST : self::API_DEFAULT_HOST;
        }

        $this->http = new Http($config);

        if ($config[self::CONFIG_DEBUG]) {
            $this->http->addDefaultLogger(
                $config[self::CONFIG_LOG_FILE_NAME],
                $config[self::CONFIG_LOG_LINE_FORMAT],
                $config[self::CONFIG_LOG_MESSAGE_FORMAT]
            );
        }
    }

    /**
     * @return \Buzzi\Http
     */
    public function getHttp()
    {
        return $this->http;
    }

    /**
     * @return \Buzzi\Support\SupportService
     */
    public function getSupportService()
    {
        if (null === $this->support) {
            $this->support = new SupportService($this->http);
        }
        return $this->support;
    }

    /**
     * @return \Buzzi\Publish\PublishService
     */
    public function getPublishService()
    {
        if (null === $this->publish) {
            $this->publish = new PublishService($this->http);
        }
        return $this->publish;
    }

    /**
     * @return \Buzzi\Consume\ConsumeService
     */
    public function getConsumeService()
    {
        if (null === $this->consume) {
            $this->consume = new ConsumeService($this->http);
        }
        return $this->consume;
    }
}
