<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Log\Monolog;

use Df;

use Df\Core\ILogger;
use Df\Core\Log\IFactory;
use Df\Core\Log\Logger;
use Df\Core\Config\Repository;

class Factory implements IFactory
{
    /**
     * Take list of config options and create global logger object
     */
    public function createLoggerFromConfig(Repository $config): ILogger
    {
        $output = new Logger();
        //dd($config, $output);

        return $output;
    }
}
