<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch;

use Df\Core\IApp;
use Df\Arch\Uri as ArchUri;
use Df\Http\Uri as HttpUri;

use Df\Plug\TContext;
use Df\Plug\IContext;

use Psr\Http\Message\ServerRequestInterface;

class Context
{
    public $request;
    public $httpRequest;
    public $app;

    /**
     * Init with http request and location uri
     */
    public function __construct(IApp $app, Uri $request, ServerRequestInterface $httpRequest=null)
    {
        $this->app = $app;
        $this->request = $request;
        $this->httpRequest = $httpRequest ?? $app[ServerRequestInterface::class];
    }

    /**
     * Temporary uri router
     */
    public function uri($uri): HttpUri
    {
        $handler = $this->app['arch.pipeline.handler'];
        return $handler->routeOut(ArchUri::instance($uri));
    }
}
