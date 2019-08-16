<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch;

use Df;

use Df\Arch\Uri as ArchUri;
use Df\Arch\Route\Named as NamedRoute;
use Df\Arch\Route\Node as NodeRoute;
use Df\Arch\Route\View as ViewRoute;

abstract class Router
{
    protected $area;
    protected $routes = [];

    /**
     * Init with area string
     */
    public function __construct(string $area)
    {
        $this->area = $area;
    }


    /**
     * Load routes into collection
     */
    abstract protected function setup(): void;


    /**
     * Convert Http request to arch uri
     */
    public function matchIn(string $method, string $path): ?IRoute
    {
        if (empty($this->routes)) {
            $this->setup();
        }

        foreach ($this->routes as $route) {
            if ($output = $route->matchIn($method, $path)) {
                return $output;
            }
        }

        return null;
    }


    /**
     * Match arch Uri against list of routes
     */
    public function matchOut(ArchUri $uri): ?IRoute
    {
        if (empty($this->routes)) {
            $this->setup();
        }

        $match = $uri->getRouteId();

        if (isset($this->routes[$match])) {
            return clone $this->routes[$match];
        }

        $type = $uri->getRouteType();

        if ($type !== 'any') {
            return null;
        }

        [,$match] = explode('://', $match);
        $pattern = '#^[^:]+://'.preg_quote($match).'$#';

        foreach ($this->routes as $id => $route) {
            if (preg_match($pattern, $id)) {
                $this->routes['any://'.$match] = $route;
                return clone $route;
            }
        }

        return null;
    }





    /**
     * Bind a named route to OPTIONS method
     */
    public function options(string $path, $name, callable $runner=null): NamedRoute
    {
        return $this->name(['OPTIONS'], $path, $name, $runner);
    }

    /**
     * Bind a named route to GET method
     */
    public function get(string $path, $name, callable $runner=null): NamedRoute
    {
        return $this->name(['GET', 'HEAD'], $path, $name, $runner);
    }

    /**
     * Bind a named route to POST method
     */
    public function post(string $path, $name, callable $runner=null): NamedRoute
    {
        return $this->name(['POST'], $path, $name, $runner);
    }

    /**
     * Bind a named route to PUT method
     */
    public function put(string $path, $name, callable $runner=null): NamedRoute
    {
        return $this->name(['PUT'], $path, $name, $runner);
    }

    /**
     * Bind a named route to DELETE method
     */
    public function delete(string $path, $name, callable $runner=null): NamedRoute
    {
        return $this->name(['DELETE'], $path, $name, $runner);
    }

    /**
     * Bind a named route to PATCH method
     */
    public function patch(string $path, $name, callable $runner=null): NamedRoute
    {
        return $this->name(['PATCH'], $path, $name, $runner);
    }

    /**
     * Bind a named route to any method
     */
    public function any(string $path, $name, callable $runner=null): NamedRoute
    {
        return $this->name(null, $path, $name, $runner);
    }

    /**
     * Bind a named route
     */
    public function name(?array $methods, string $path, $name, callable $runner=null): NamedRoute
    {
        if ($runner === null) {
            if (is_callable($name)) {
                $runner = $name;
                $name = $path;
            } else {
                throw \Glitch::EInvalidArgument(
                    'Named routes must define a runner function'
                );
            }
        }

        return $this->addRoute(new NamedRoute($methods, $path, $name, $runner));
    }



    /**
     * Bind path to node handler
     */
    public function node(string $path, string $node): NodeRoute
    {
        return $this->addRoute(new NodeRoute($path, $node));
    }



    /**
     * Add a new route to the list
     */
    public function addRoute(IRoute $route): IRoute
    {
        $id = $route->getRouteType().'://'.ltrim($route->getRoutePath(), '/');
        return $this->routes[$id] = $route;
    }
}
