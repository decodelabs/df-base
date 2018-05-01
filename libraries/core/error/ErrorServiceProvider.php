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
use df\core\service\IContainer;
use df\core\service\IProvider;

class ErrorServiceProvider implements IProvider
{
    public static function getProvidedServices(): array
    {
        return [
            error\IHandler::class,
            error\IReporter::class
        ];
    }

    public function registerServices(IContainer $app): void
    {
        $app->bindShared(error\IHandler::class, error\Handler::class);
        $app->bind(error\IReporter::class, error\reporter\Whoops::class);
    }
}
