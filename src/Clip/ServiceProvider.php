<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip;

use Df;

use Df\Core\Service\IContainer;
use Df\Core\Service\IProvider;

use Df\Clip\Command\Factory;
use Df\Clip\Command\IRequest;

class ServiceProvider implements IProvider
{
    /**
     * Get list of provided classes
     */
    public static function getProvidedServices(): array
    {
        return [
            Factory::class,
            IRequest::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(IContainer $app): void
    {
        // Factory
        $app->bindShared(Factory::class);

        // Request
        $app->bindShared(IRequest::class, function ($app, Factory $factory) {
            return $factory->fromEnvironment();
        })->alias('clip.request');
    }
}
