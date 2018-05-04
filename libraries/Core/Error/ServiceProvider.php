<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Error;

use Df;

use Df\Core\Error\IHandler;
use Df\Core\Error\Handler;

use Df\Core\Error\IReporter;
use Df\Core\Error\Reporter\Whoops;

use Df\Core\Service\IContainer;
use Df\Core\Service\IProvider;

class ServiceProvider implements IProvider
{
    const AUTO_REGISTER = true;

    /**
     * Get list of provided classes
     */
    public static function getProvidedServices(): array
    {
        return [
            IHandler::class,
            IReporter::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(IContainer $app): void
    {
        $app->bindShared(IHandler::class, Handler::class);
        $app->bind(IReporter::class, Whoops::class);

        Handler::register($app[IHandler::class]);
    }
}
