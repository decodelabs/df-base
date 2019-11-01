<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Crypt;

use Df\Core\Crypt\Symmetric;
use Df\Core\Crypt\Symmetric\Halite as HaliteSymmetric;
use Df\Core\Crypt\Hasher;
use Df\Core\Crypt\Hasher\Native as NativeHasher;

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
            Symmetric::class,
            Hasher::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(Container $app): void
    {
        // Symmetric
        $app->bindShared(Symmetric::class, function ($app) {
            return new HaliteSymmetric($app->getBasePath().'/private/halite.key');
        });

        // Hasher
        $app->bindShared(Hasher::class, function ($app) {
            $config = $app['core.config.repository'];
            $options = [];

            if (null !== ($algo = $config['crypt.algo'])) {
                $options = $config->crypt->{$algo}->toArray();
            }

            return new NativeHasher($algo, $options);
        });
    }
}
