<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Cache;

use Df\Core\Service\Container;
use Df\Core\Service\Provider;

class ServiceProvider implements Provider
{
    /**
     * Get list of provided classes
     */
    public static function getProvidedServices(): array
    {
        return [
            Manager::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(Container $app): void
    {
        $app->bindOnce(Manager::class, function ($app) {
            $config = $app['core.config.repository'];
            return new Manager($config->cache);
        });
    }
}
