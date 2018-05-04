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
    /**
     * Load routes into collection
     */
    abstract public function setup(): void;


    /**
     * Convert Http request to arch uri
     */
    public function routeIn(string $method, string $path): IRoute
    {
        dd($this, $method, $path);
    }
}
