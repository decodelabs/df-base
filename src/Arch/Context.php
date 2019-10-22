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

class Context implements IContext
{
    use TContext;

    protected $request;
    protected $httpRequest;

    /**
     * Init with http request and location uri
     */
    public function __construct(IApp $app, Uri $request, ServerRequestInterface $httpRequest=null)
    {
        $this->app = $app;
        $this->request = $request;
        $this->httpRequest = $httpRequest ?? $app[ServerRequestInterface::class];
    }
}
