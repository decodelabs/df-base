<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Http\Pipeline;

use Df;

use Df\Core\IApp;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class Dispatcher implements IDispatcher
{
    protected $queue = [];
    protected $app;


    /**
     * Init with middleware queue
     */
    public function __construct(IApp $app, array $middlewares=[])
    {
        $this->app = $app;
        $this->queueList($middlewares);
    }


    /**
     * Add middleware to queue
     */
    public function queue(MiddlewareInterface $middleware): IDispatcher
    {
        $this->queue[] = $middleware;
        return $this;
    }

    /**
     * Add callback to middleware queue
     */
    public function queueCallable(callable $callable): IDispatcher
    {
        return $this->queue(new CallableMiddleware($callable));
    }

    /**
     * Add middleware by type
     */
    public function queueType(string $type): IDispatcher
    {
        $ref = new \ReflectionClass($type);

        if (!$ref->implementsInterface('Psr\Http\Server\MiddlewareInterface')) {
            throw Glitch::EImplementation(
                'Queued middleware "'.$type.'" does not implement MiddlewareInterface'
            );
        }

        return $this->queueCallable(function ($request, $handler) use ($type) {
            $middleware = $this->app->newInstanceOf($type);
            return $middleware->process($request, $handler);
        });
    }

    /**
     * Queue list of middlewares
     */
    public function queueList(array $middlewares): IDispatcher
    {
        foreach ($middlewares as $middleware) {
            if ($middleware instanceof MiddlewareInterface) {
                $this->queue($middleware);
            } elseif (is_callable($middleware)) {
                $this->queueCallable($middleware);
            } elseif (is_string($middleware)) {
                $this->queueType($middleware);
            } else {
                throw Glitch::EInvalidArgument(
                    'Unexpected / invalid middleware type',
                    null,
                    $middleware
                );
            }
        }

        return $this;
    }


    /**
     * Entry point to dispatcher
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = current($this->queue);
        next($this->queue);

        if (!$middleware) {
            throw Glitch::ENotFound([
                'message' => 'Reached the end of the middleware queue without a response',
                'http' => 404
            ]);
        }

        return $middleware->process($request, $this);
    }

    /**
     * Passthrough for callbacks
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->handle($request);
    }


    /**
     * Shutdown middleware
     */
    public function terminate(ServerRequestInterface $request, ResponseInterface $response): void
    {
        foreach ($this->queue as $middleware) {
            if ($middleware instanceof ITerminable) {
                $middleware->terminate($request, $response);
            }
        }
    }
}
