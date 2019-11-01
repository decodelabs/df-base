<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core;

use Psr\Log\LoggerInterface;

interface Logger extends LoggerInterface
{
    public function addChannel(string $name, LoggerInterface $channel): Logger;
    public function setDefaultChannel(string $name): Logger;
    public function onChannel(string $name): LoggerInterface;
    public function removeChannel(string $name): Logger;
    public function clearChannels(): Logger;
}
