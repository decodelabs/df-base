<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Logger;

use Df\Core\Logger;
use Df\Core\Logger\Factory;

use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

use DecodeLabs\Exceptional;

class Generic implements Logger
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

    protected $default;
    protected $channels = [];
    protected $factory;

    /**
     * Init with factory
     */
    public function __construct(Factory $factory, string $default=null)
    {
        $this->factory = $factory;
        $this->default = $default ?? 'default';
    }

    /**
     * Register a channel (instanceof Psr\Log\LoggerInterface)
     */
    public function addChannel(string $name, LoggerInterface $channel): Logger
    {
        $this->channels[$name] = $channel;

        if (!isset($this->default)) {
            $this->default = $name;
        }

        return $this;
    }

    /**
     * Set existing channel as default
     */
    public function setDefaultChannel(string $name): Logger
    {
        $this->default = $name;
        return $this;
    }

    /**
     * Return registered channel to log to
     */
    public function onChannel(string $name): LoggerInterface
    {
        if (!isset($this->channels[$name])) {
            try {
                $channel = $this->factory->loadChannel($name);
            } catch (\Throwable $e) {
                $channel = $this->factory->createEmergencyChannel();

                $channel->emergency('Loading of channel "'.$name.'" failed - falling back on emergency logger', [
                    'exception' => $e
                ]);
            }

            $this->addChannel($name, $channel);
        }

        return $this->channels[$name];
    }

    /**
     * Remove registered channel
     */
    public function removeChannel(string $name): Logger
    {
        unset($this->channels[$name]);

        if ($this->default === $name) {
            $this->default = key($this->channels);
        }

        return $this;
    }

    /**
     * Remove all registered channels
     */
    public function clearChannels(): Logger
    {
        $this->channels = [];
        $this->default = null;
        return $this;
    }


    /**
     * Defer the log to registered channels
     */
    public function log($level, $message, array $context=[])
    {
        if (!isset(self::LEVELS[$level])) {
            throw Exceptional::{'InvalidArgument,Psr\\Log\\InvalidArgumentException'}(
                'Invalid log level: '.$level
            );
        }

        $this->onChannel($this->default)->log($level, $message, $context);
    }
}
