<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\http\pipeline;

use df;
use df\http;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

interface IDispatcher extends RequestHandlerInterface
{
    public function queue(MiddlewareInterface $middleware): IDispatcher;
    public function queueCallable(callable $callable): IDispatcher;
    public function queueType(string $middleware): IDispatcher;
    public function queueList(array $middlewares): IDispatcher;
}
