<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch\Route;

use Df;
use Df\Core\IApp;

use Df\Arch\IRoute;
use Df\Arch\Uri;

use Df\Http\Response\Stream;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Named implements IRoute
{
    use TRoute;

    protected $name;
    protected $methods = [];
    protected $runner;

    /**
     * Init with name and methods
     */
    public function __construct(?array $methods, string $path, string $name, callable $runner)
    {
        $this->name = $name;
        $this->path = $path;
        $this->methods = $methods;
        $this->runner = $runner;
    }


    /**
     * Use $path as unique id
     */
    public function getId(): string
    {
        return 'name://'.$this->name;
    }

    /**
     * Get internal URI representation of request URI
     */
    public function getUri(): Uri
    {
        $output = 'name://'.$this->name.'?'.http_build_query($this->params);
        dd($output);
        Df\incomplete();
    }



    /**
     * Match request $path to route path
     */
    public function matchIn(string $method, string $requestPath): ?IRoute
    {
        if ($this->methods !== null && !in_array($method, $this->methods)) {
            return null;
        }

        return $this->matchPath($requestPath);
    }


    /**
     * Dispatch to response
     */
    public function dispatch(ServerRequestInterface $request, IApp $app): ResponseInterface
    {
        $params = $this->params;
        $params['route'] = $this;

        $output = $app->call($this->runner, $params);
        return $this->normalizeResponse($output, $app);
    }
}
