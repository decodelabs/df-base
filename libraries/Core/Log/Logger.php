<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Log;

use Df;
use Df\Core\ILogger;

use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Logger implements ILogger
{
    use LoggerTrait;

    const LEVELS = [
        'debug' => LogLevel::DEBUG,
        'info' => LogLevel::INFO,
        'notice' => LogLevel::NOTICE,
        'warning' => LogLevel::WARNING,
        'error' => LogLevel::ERROR,
        'critical' => LogLevel::CRITICAL,
        'alert' => LogLevel::ALERT,
        'emergency' => LogLevel::EMERGENCY,
    ];

    protected $channels = [];


    /**
     * Register a channel (instanceof Psr\Log\LoggerInterface)
     */
    public function addChannel(string $name, LoggerInterface $channel): ILogger
    {
        $this->channels[$name] = $channel;
        return $this;
    }

    /**
     * Return registered channel to log to
     */
    public function onChannel(string $name): LoggerInterface
    {
        if (!isset($this->channels[$name])) {
            throw Df\Error::ENotFound(
                'Logger channel "'.$name.'" has not been defined'
            );
        }

        return $this->channels[$name];
    }

    /**
     * Remove registered channel
     */
    public function removeChannel(string $name): ILogger
    {
        unset($this->channels[$name]);
        return $this;
    }

    /**
     * Remove all registered channels
     */
    public function clearChannels(): ILogger
    {
        $this->channels = [];
        return $this;
    }


    /**
     * Defer the log to registered channels
     */
    public function log($level, $message, array $context=[])
    {
        if (!isset(self::LEVELS[$level])) {
            throw Df\Error::{'EInvalidArgument,Psr\Log\InvalidArgumentException'}(
                'Invalid log level: '.$level
            );
        }

        foreach ($this->channels as $channel) {
            $channel->log($level, $message, $context);
        }
    }
}
