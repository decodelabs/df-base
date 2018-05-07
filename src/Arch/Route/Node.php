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
use Df\Arch\Context;

use Df\Http\Response\Stream;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Node implements IRoute
{
    use TRoute;

    protected $nodePath;

    /**
     * Init with request path and node path
     */
    public function __construct(string $path, string $nodePath)
    {
        $this->path = $path;
        $this->nodePath = ltrim($nodePath, '/');
    }


    /**
     * Use uri path as unique id
     */
    public function getRouteType(): string
    {
        return 'node';
    }

    /**
     * Get uri path
     */
    public function getRoutePath(): string
    {
        return $this->nodePath;
    }


    /**
     * Match request $path to route path
     */
    public function matchIn(string $method, string $requestPath): ?IRoute
    {
        return $this->matchPath($requestPath);
    }

    /**
     * Dispatch to response
     */
    public function dispatch(Context $context): ResponseInterface
    {
        $path = ltrim($context->request->path, '/');

        if (substr($path, -1) == '/') {
            $path .= 'index';
        }

        $parts = explode('/', $path);
        $parts = array_map('ucfirst', $parts);
        $class = '\\Df\\Apex\\Http\\'.ucfirst($context->request->area).'\\'.implode('\\', $parts).'Node';

        if (!class_exists($class, true)) {
            throw Df\Error::ENotFound([
                'message' => 'Node not found: '.$context->request,
                'http' => 404,
                'data' => $context->request
            ]);
        }

        $node = $context->app->newInstanceOf($class, [
            'route' => $this,
            'context' => $context
        ]);

        $output = $node->dispatch();
        return $this->normalizeResponse($output, $context->app);
    }
}
