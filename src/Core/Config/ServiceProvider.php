<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Config;

use Df\Core\Config\Loader\PhpArray;
use Df\Core\Config\EnvLoader\DotIni;

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
            Env::class,
            Repository::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(IContainer $app): void
    {
        // Env
        $app->bindOnce(IEnvLoader::class, DotIni::class)
            ->inject('path', $app->getBasePath().'/private/.env');

        $app->bindShared(Env::class, function ($app, IEnvLoader $loader) {
            return $loader->loadEnvConfig($app);
        });



        // Config
        $app->bindOnce(ILoader::class, PhpArray::class);

        $app->bindShared(Repository::class, function ($app, ILoader $loader) {
            return $loader->loadConfig($app);
        });
    }
}
