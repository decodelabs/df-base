<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch;

use Df;

use Df\Arch\Pipeline\AreaMap;
use Df\Arch\Pipeline\IHandler;
use Df\Arch\Pipeline\Handler;

use Df\Core\Config\Repository;
use Df\Core\ILoader;

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
            IHandler::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(IContainer $app): void
    {
        $app->bindShared(IHandler::class, Handler::class)
            ->prepareWith(function ($handler, $app) {
                // Area maps
                $config = $app[Repository::class];
                $handler->loadAreaMaps($config->arch->areaMaps->toArray());

                // Routers
                $bundles = $app[ILoader::class]->getLoadedBundles();
                $handler->setRouterBundles($bundles);

                return $handler;
            });
    }
}
