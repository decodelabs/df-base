<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip;

use Df;
use Df\Core\IApp;
use Df\Clip\IRequest;
use Df\Clip\IShell;

use Df\Plug\TContext;
use Df\Plug\IContext;

class Context implements IContext
{
    use TContext;

    protected $request;
    protected $shell;

    /**
     * Init with http request and location uri
     */
    public function __construct(IApp $app, IRequest $request, IShell $shell)
    {
        $this->app = $app;
        $this->request = $request;
        $this->shell = $shell;
    }

    /**
     * Pass calls through to shell
     */
    public function __call(string $method, array $args)
    {
        return $this->shell->{$method}(...$args);
    }
}
