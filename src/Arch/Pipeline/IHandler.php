<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Arch\Pipeline;

use Df\Arch\Uri as ArchUri;
use Df\Http\Uri as HttpUri;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

interface IHandler extends MiddlewareInterface
{
    public function loadAreaMaps(array $maps): IHandler;
    public function addAreaMap(AreaMap $map): IHandler;

    public function setRouterBundles(array $bundles): IHandler;
    public function loadRouters(string $area): IHandler;

    public function routeIn(ServerRequestInterface $request): ?ResponseInterface;
    public function routeOut(ArchUri $uri): HttpUri;
}
