<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Error\Reporter;

use Df;
use Df\Core\Error\IReporter;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

class Whoops implements IReporter
{
    /**
     * Report a caught exception
     */
    public function reportException(\Throwable $exception)
    {
        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());

        $whoops->handleException($exception);
    }
}
