<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Arch;

use Df\Arch\Uri;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Route
{
    public function getRouteType(): string;
    public function getRoutePath(): string;

    public function buildUri(ServerRequestInterface $request): Uri;

    public function matchIn(string $method, string $requestPath): ?Route;
    public function routeOut(Uri $uri): string;

    public function mergeParams(array $params): Route;
    public function dispatch(Context $context): ResponseInterface;
}
