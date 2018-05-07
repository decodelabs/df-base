<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Arch;

use Df;
use Df\Core\IApp;

use Df\Arch\Uri;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IRoute
{
    public function getRouteType(): string;
    public function getRoutePath(): string;

    public function buildUri(ServerRequestInterface $request): Uri;

    public function matchIn(string $method, string $requestPath): ?IRoute;
    public function routeOut(Uri $uri): string;

    public function mergeParams(array $params): IRoute;
    public function dispatch(Context $context): ResponseInterface;
}
