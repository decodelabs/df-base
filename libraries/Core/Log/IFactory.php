<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Log;

use Df;

use Df\Core\ILogger;
use Df\Core\Config\Repository;

interface IFactory
{
    public function createLoggerFromConfig(Repository $config): ILogger;
}
