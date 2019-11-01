<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Logger;

use Df\Core\Logger;
use Df\Core\Config\Repository;

use Psr\Log\LoggerInterface;

interface Factory
{
    public function loadChannel(string $name): LoggerInterface;
    public function createChannel(string $name, string $type, Repository $config): LoggerInterface;
    public function createEmergencyChannel(string $name=null): LoggerInterface;
}
