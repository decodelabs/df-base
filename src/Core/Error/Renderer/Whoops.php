<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Error\Renderer;

use Df;
use Df\Core\Error\IRenderer;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

class Whoops implements IRenderer
{
    /**
     * Report a caught exception
     */
    public function renderException(\Throwable $exception)
    {
        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());

        $whoops->handleException($exception);
    }
}
