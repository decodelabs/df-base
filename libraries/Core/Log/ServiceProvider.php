<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Log;

use Df;

use Df\Core\ILogger;
use Df\Core\Log\Logger;
use Df\Core\Log\Monolog\Factory as MonologFactory;

use Df\Core\Service\IContainer;
use Df\Core\Service\IProvider;

class ServiceProvider implements IProvider
{
    /**
     * Get list of provided classes
     */
    public static function getProvidedServices(): array
    {
        return [
            IFactory::class,
            ILogger::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(IContainer $app): void
    {
        $app->bind(IFactory::class, MonologFactory::class);

        $app->bindShared(ILogger::class, Logger::class);
    }
}
