<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Logger\Factory;

use Df\Core\App;
use Df\Core\Logger\Factory;
use Df\Core\Config\Repository;

use DecodeLabs\Dictum;

use Psr\Log\LoggerInterface;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\ChromePHPHandler;

class Monolog implements Factory
{
    protected $config;
    protected $app;

    /**
     * Init with config
     */
    public function __construct(App $app, Repository $config)
    {
        $this->config = $config->logging;
        $this->app = $app;
    }

    /**
     * Lookup channel in config and create
     */
    public function loadChannel(string $name): LoggerInterface
    {
        $config = $this->config->{$name};
        $type = $config['type'] ?? $name;

        try {
            return $this->createChannel($name, $type, $config);
        } catch (\Throwable $e) {
            return $this->createEmergencyChannel($name);
        }
    }


    /**
     * Attempt to create a config base channel
     */
    public function createChannel(string $name, string $type, Repository $config): LoggerInterface
    {
        $handlers = $this->createHandlers($name, $type, $config);
        return new Logger($name, $handlers);
    }

    protected function createHandlers(string $name, string $type, Repository $config): array
    {
        switch ($type) {
            case 'group':
                return $this->createGroupHandlers($name, $config);

            case 'stream':
                return [$this->createStreamHandler($name, $config)];

            case 'daily':
                return [$this->createDailyHandler($name, $config)];

            case 'slack':
                return [$this->createSlackHandler($name, $config)];

            case 'syslog':
                return [$this->createSyslogHandler($name, $config)];

            case 'errorlog':
                return [$this->createErrorLogHandler($name, $config)];

            case 'monolog':
                return [$this->createMonologHandler($name, $config)];

            default:
                return [$this->createDailyHandler($name, new Repository())];
        }
    }


    /**
     * Create stacked group channel
     */
    protected function createGroupHandlers(string $name, Repository $config): array
    {
        $channels = $config->channels->toArray();
        $output = [];

        if (empty($channels)) {
            $channels = ['default'];
        }

        foreach ($channels as $channel) {
            $channelConfig = $this->config->{$channel};
            $channelType = $channelConfig['type'] ?? $channel;
            $output = array_merge($output, $this->createHandlers($channel, $channelType, $channelConfig));
        }

        return $output;
    }

    /**
     * Create stream based channel
     */
    protected function createStreamHandler(string $name, Repository $config): HandlerInterface
    {
        return new StreamHandler(
            $config['path'] ?? $this->app->getStoragePath().'/logs/'.Dictum::fileName($name).'.log',
            $config['level'] ?? Logger::DEBUG,
            $config['bubble'] ?? true,
            $config['permission'] ?? null,
            $config['locking'] ?? false
        );
    }

    /**
     * Create daily rotation channel
     */
    protected function createDailyHandler(string $name, Repository $config): HandlerInterface
    {
        return new RotatingFileHandler(
            $config['path'] ?? $this->app->getStoragePath().'/logs/'.Dictum::fileName($name).'.log',
            $config['maxFiles'] ?? 7,
            $config['level'] ?? Logger::DEBUG,
            $config['bubble'] ?? true,
            $config['permission'] ?? null,
            $config['locking'] ?? false
        );
    }

    /**
     * Create slack channel
     */
    protected function createSlackHandler(string $name, Repository $config): HandlerInterface
    {
        return new SlackWebhookHandler(
            $config['url'],
            $config['channel'] ?? null,
            $config['username'] ?? 'Decode Framework',
            $config['attachment'] ?? true,
            $config['emoji'] ?? ':boom:',
            $config['short'] ?? false,
            $config['context'] ?? true,
            $config['level'] ?? Logger::DEBUG
        );
    }


    /**
     * Create syslog channel
     */
    protected function createSyslogHandler(string $name, Repository $config): HandlerInterface
    {
        return new SyslogHandler(
            $this->app->getAppName(),
            $config['facility'] ?? LOG_USER,
            $config['level'] ?? Logger::DEBUG,
            $config['bubble'] ?? true
        );
    }

    /**
     * Create native error log channel
     */
    protected function createErrorLogHandler(string $name, Repository $config): HandlerInterface
    {
        return new ErrorLogHandler(
            $config['messageType'] ?? ErrorLogHandler::OPERATING_SYSTEM,
            $config['level'] ?? Logger::DEBUG,
            $config['bubble'] ?? true
        );
    }

    /**
     * Create chrome php channel
     */
    protected function createMonologHandler(string $name, Repository $config): HandlerInterface
    {
        $type = $config['handler'];
        $params = $config->params->toArray();
        return $this->app->newInstanceOf($type, $params);
    }


    /**
     * Create dependable channel
     */
    public function createEmergencyChannel(string $name=null): LoggerInterface
    {
        return new Logger($name ?? 'emergency', [new RotatingFileHandler(
            $this->app->getStoragePath().'/logs/emergency.log',
            3,
            Logger::DEBUG
        )]);
    }
}
