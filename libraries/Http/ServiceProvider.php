<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Http;

use Df;

use Df\Core\Service\IContainer;
use Df\Core\Service\IProvider;

use Df\Http\Request\Factory;
use Df\Http\Response\Sender;
use Df\Http\Response\ISender;
use Df\Http\pipeline\Dispatcher;
use Df\Http\pipeline\IDispatcher;

use Psr\Http\Message\ServerRequestInterface;

class ServiceProvider implements IProvider
{
    /**
     * Get list of provided classes
     */
    public static function getProvidedServices(): array
    {
        return [
            ServerRequestInterface::class,
            IDispatcher::class,
            ISender::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(IContainer $app): void
    {
        // Server request
        $app->bindShared(ServerRequestInterface::class, function ($app) {
            return (new Factory())->createFromEnvironment();
        })->alias('http.request.server');

        // Dispatcher
        $app->bind(IDispatcher::class, Dispatcher::class);

        // Response sender
        $app->bind(ISender::class, Sender::class);
    }
}
