<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\http;

use df;

use df\core\service\IContainer;
use df\core\service\IProvider;

use df\http;
use df\http\request\Factory;
use df\http\response\Sender;
use df\http\response\ISender;
use df\http\pipeline\Dispatcher;
use df\http\pipeline\IDispatcher;

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