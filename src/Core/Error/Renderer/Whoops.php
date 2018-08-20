<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Error\Renderer;

use Df;
use Df\Core\Error\IRenderer;
use Df\Core\IApp;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\PlainTextHandler;

class Whoops implements IRenderer
{
    public $app;

    /**
     * Setup renderer
     */
    public function __construct(IApp $app)
    {
        $this->app = $app;
    }

    /**
     * Can this renderer be loaded?
     */
    public static function isLoadable(): bool
    {
        return class_exists(Run::class, true);
    }

    /**
     * Report a caught exception
     */
    public function renderException(\Throwable $exception)
    {
        $whoops = new Run();

        if ($this->app->{'core.kernel.http'}->hasInstance()) {
            $whoops->pushHandler(new PrettyPageHandler());
        } else {
            $whoops->pushHandler(new PlainTextHandler());
        }

        $whoops->handleException($exception);
    }
}
