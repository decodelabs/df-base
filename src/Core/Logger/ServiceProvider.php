<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Logger;

use Df\Core\Logger;
use Df\Core\Logger\Generic as GenericLogger;
use Df\Core\Logger\Factory;
use Df\Core\Logger\Factory\Monolog as MonologFactory;

use Df\Core\Service\Container;
use Df\Core\Service\Provider;

use DecodeLabs\Glitch;

class ServiceProvider implements Provider
{
    /**
     * Get list of provided classes
     */
    public static function getProvidedServices(): array
    {
        return [
            Factory::class,
            Logger::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(Container $app): void
    {
        $app->bind(Factory::class, MonologFactory::class);

        $app->bindShared(Logger::class, GenericLogger::class)
            ->prepareWith(function ($logger) {
                Glitch::setLogger($logger);
                return $logger;
            });
    }
}
