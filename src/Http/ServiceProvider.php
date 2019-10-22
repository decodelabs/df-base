<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Http;

use Df\Core\Service\IContainer;
use Df\Core\Service\IProvider;

use Df\Http\Request\Factory;
use Df\Http\Response\Sender;
use Df\Http\Response\ISender;
use Df\Http\Pipeline\Dispatcher;
use Df\Http\Pipeline\IDispatcher;

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
            return (new Factory())->fromEnvironment();
        })->alias('http.request.server');

        // Dispatcher
        $app->bind(IDispatcher::class, Dispatcher::class);

        // Response sender
        $app->bind(ISender::class, Sender::class)
            ->prepareWith(function ($sender, $app) {
                $config = $app['core.config.repository'];

                $sender->setSendfileHeader($config['http.sendfile']);
                $sender->setManualChunk($config['http.manualChunk']);

                return $sender;
            });
    }
}
