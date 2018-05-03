<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\core\error;

use df;

use df\core;

use df\core\error\IHandler;
use df\core\error\Handler;

use df\core\error\IReporter;
use df\core\error\reporter\Whoops;

use df\core\service\IContainer;
use df\core\service\IProvider;

class ErrorServiceProvider implements IProvider
{
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
    }
}
