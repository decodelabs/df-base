<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Config;

use Df\Core\IApp;

use Df\Core\Config\EnvLoader;
use Df\Core\Config\EnvLoader\DotIni;
use Df\Core\Config\Loader;
use Df\Core\Config\Loader\PhpArray;

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
        if (!$app instanceof IApp) {
            throw Glitch::EUnexpectedValue('Container is not the app', null, $app);
        }

        // Env
        $app->bindOnce(EnvLoader::class, DotIni::class)
            ->inject('path', $app->getBasePath().'/private/.env');

        $app->bindShared(Env::class, function ($app, EnvLoader $loader) {
            return $loader->loadEnvConfig($app);
        });



        // Config
        $app->bindOnce(Loader::class, PhpArray::class);

        $app->bindShared(Repository::class, function ($app, Loader $loader) {
            return $loader->loadConfig($app);
        });
    }
}
