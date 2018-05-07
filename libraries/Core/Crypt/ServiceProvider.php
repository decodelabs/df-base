<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Crypt;

use Df;

use Df\Core\Crypt\Symmetric\Halite;

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
            ISymmetric::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(IContainer $app): void
    {
        // Symmetric
        $app->bindShared(ISymmetric::class, function ($app) {
            return new Halite($app->getBasePath().'/private/halite.key');
        });
    }
}
