<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\core\error\reporter;

use df;
use df\core;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

class Whoops implements core\error\IReporter
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
