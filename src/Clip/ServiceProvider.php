<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip;

use Df\Core\Service\IContainer;
use Df\Core\Service\IProvider;

use DecodeLabs\Terminus\Cli;
use DecodeLabs\Terminus\Command\Request;

class ServiceProvider implements IProvider
{
    /**
     * Get list of provided classes
     */
    public static function getProvidedServices(): array
    {
        return [
            Request::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(IContainer $app): void
    {
        // Request
        $app->bindShared(Request::class, function ($app) {
            $args = $_SERVER['argv'];
            $script = array_shift($args);
            return Cli::newRequest($args);
        })->alias('clip.request');
    }
}
