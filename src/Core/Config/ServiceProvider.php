<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Config;

use Df\Core\App;

use Df\Core\Config\EnvLoader;
use Df\Core\Config\EnvLoader\DotIni;
use Df\Core\Config\Loader;
use Df\Core\Config\Loader\PhpArray;

use Df\Core\Service\Container;
use Df\Core\Service\Provider;

use DecodeLabs\Exceptional;

class ServiceProvider implements Provider
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
    public function registerServices(Container $app): void
    {
        if (!$app instanceof App) {
            throw Exceptional::UnexpectedValue(
                'Container is not the app', null, $app
            );
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
