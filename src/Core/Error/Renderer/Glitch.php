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

class Glitch implements IRenderer
{
    public $app;

    /**
     * Setup renderer
     */
    public function __construct(IApp $app)
    {
        $this->app = $app;
        \Glitch::setAutoRegister(false);
    }

    /**
     * Can this renderer be loaded?
     */
    public static function isLoadable(): bool
    {
        return class_exists(\Glitch::class, true);
    }

    /**
     * Report a caught exception
     */
    public function renderException(\Throwable $exception)
    {
        \Glitch::getContext()->handleException($exception);
    }
}
