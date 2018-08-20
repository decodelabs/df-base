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

use Df\Clip\Request\Factory;

class ServiceProvider implements IProvider
{
    /**
     * Get list of provided classes
     */
    public static function getProvidedServices(): array
    {
        return [
            IRequest::class,
            IDispatcher::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(IContainer $app): void
    {
        // Request
        $app->bindShared(IRequest::class, function ($app) {
            return (new Factory())->fromEnvironment();
        })->alias('clip.request');

        // Dispatcher
        $app->bind(IDispatcher::class, Dispatcher::class);
    }
}