<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch;

use Df;

use Psr\Http\Message\ServerRequestInterface;

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
}
