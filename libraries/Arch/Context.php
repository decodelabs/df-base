<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch;

use Df;
use Df\Core\IApp;
use Df\Arch\Uri as ArchUri;
use Df\Http\Uri as HttpUri;

use Psr\Http\Message\ServerRequestInterface;

class Context
{
    protected $request;
    protected $httpRequest;
    protected $app;

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
     * Get protected member
     */
    public function __get(string $name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }

        // TODO: load helper
        dd($name);
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
