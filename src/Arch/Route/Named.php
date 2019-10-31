<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch\Route;

use Df\Core\IApp;
use Df\Arch\Route;
use Df\Arch\RouteTrait;
use Df\Arch\Context;
use Df\Arch\Uri;

use Df\Http\Response\Stream;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Named implements Route
{
    use RouteTrait;

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
     * Use uri path as unique id
     */
    public function getRouteType(): string
    {
        return 'name';
    }

    /**
     * Get uri path
     */
    public function getRoutePath(): string
    {
        return $this->name;
    }


    /**
     * Match request $path to route path
     */
    public function matchIn(string $method, string $requestPath): ?Route
    {
        if ($this->methods !== null && !in_array($method, $this->methods)) {
            return null;
        }

        return $this->matchPath($requestPath);
    }


    /**
     * Dispatch to response
     */
    public function dispatch(Context $context): ResponseInterface
    {
        $params = array_merge($context->httpRequest->getAttributes(), $this->params, [
            'route' => $this,
            'context' => $context
        ]);

        $output = $context->app->call($this->runner, $params);
        return $this->normalizeResponse($output, $context->app);
    }
}
