<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch;

use Df;
use Df\Arch\Route\Named;
use Df\Arch\Route\Node;
use Df\Arch\Route\View;

abstract class Router
{
    protected $routes = [];

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
     * Bind a named route to OPTIONS method
     */
    public function options(string $path, $name, callable $runner=null): IRoute
    {
        return $this->name(['OPTIONS'], $path, $name, $runner);
    }

    /**
     * Bind a named route to GET method
     */
    public function get(string $path, $name, callable $runner=null): IRoute
    {
        return $this->name(['GET', 'HEAD'], $path, $name, $runner);
    }

    /**
     * Bind a named route to POST method
     */
    public function post(string $path, $name, callable $runner=null): IRoute
    {
        return $this->name(['POST'], $path, $name, $runner);
    }

    /**
     * Bind a named route to PUT method
     */
    public function put(string $path, $name, callable $runner=null): IRoute
    {
        return $this->name(['PUT'], $path, $name, $runner);
    }

    /**
     * Bind a named route to DELETE method
     */
    public function delete(string $path, $name, callable $runner=null): IRoute
    {
        return $this->name(['DELETE'], $path, $name, $runner);
    }

    /**
     * Bind a named route to PATCH method
     */
    public function patch(string $path, $name, callable $runner=null): IRoute
    {
        return $this->name(['PATCH'], $path, $name, $runner);
    }

    /**
     * Bind a named route to any method
     */
    public function any(string $path, $name, callable $runner=null): IRoute
    {
        return $this->name(null, $path, $name, $runner);
    }

    /**
     * Bind a named route
     */
    public function name(?array $methods, string $path, $name, callable $runner=null): IRoute
    {
        if ($runner === null) {
            if (is_callable($name)) {
                $runner = $name;
                $name = $path;
            } else {
                throw Df\Error::EInvalidArgument(
                    'Named routes must define a runner function'
                );
            }
        }

        return $this->routes[] = new Named($methods, $path, $name, $runner);
    }
}
