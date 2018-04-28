<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\core\error;

use df;
use df\core;
use df\core\error;

class ErrorServiceProvider implements core\IServiceProvider
{
    public static function getProvidedServices(): array
    {
        return [
            error\IHandler::class,
            error\IReporter::class
        ];
    }

    public function registerServices(core\IContainer $app): void
    {
        $app->bindShared(error\IHandler::class, error\Handler::class);
        $app->bind(error\IReporter::class, error\reporter\Whoops::class);
    }
}
