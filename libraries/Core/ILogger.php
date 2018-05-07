<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core;

use Df;

use Psr\Log\LoggerInterface;

interface ILogger extends LoggerInterface
{
    public function addChannel(string $name, LoggerInterface $channel): ILogger;
    public function onChannel(string $name): LoggerInterface;
    public function removeChannel(string $name): ILogger;
    public function clearChannels(): ILogger;
}
