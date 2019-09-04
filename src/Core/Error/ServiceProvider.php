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

use Df\Core\Error\IRenderer;
use Df\Core\Error\Renderer;

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
            IRenderer::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(IContainer $app): void
    {
        $app->bindShared(IHandler::class, Handler::class);
        $renderers = ['Glitch', 'Whoops', 'Dump'];

        foreach ($renderers as $name) {
            $class = '\\Df\\Core\\Error\\Renderer\\'.$name;

            if ($class::isLoadable()) {
                $app->bind(IRenderer::class, $class);
                break;
            }
        }

        Handler::register($app[IHandler::class]);
    }
}
