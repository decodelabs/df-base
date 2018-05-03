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
            ->prepareWith(function ($handler) {
                // TODO: Get this from config
                $devAreas = [
                    '*' => 'df.test:8080/test/df-playground-/',
                    'admin' => 'df.test:8080/test/df-playground-/admin/',
                    'shared' => 'df.test:8080/test/df-playground-/~{name-test}/{stuff}',
                    'devtools' => 'devtools.df.test:8080/test/df-playground-/'
                ];

                $handler->loadAreaMaps($devAreas);
                return $handler;
            });
    }
}
