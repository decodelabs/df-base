<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch\Middleware;

use DecodeLabs\Glitch\Stack\Trace;
use DecodeLabs\Exceptional;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class ErrorHandler implements MiddlewareInterface
{
    /**
     * Catch throwables and show something useful
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $e) {
            return $this->renderError($e);
        }
    }

    /**
     * Render details of an exception thrown further down the stack
     */
    public function renderError(\Throwable $e): ResponseInterface
    {
        if ($e instanceof Exceptional\Exception) {
            $http = $e->getHttpStatus() ?? 500;
            $trace = $e->getStackTrace();
        } else {
            $http = 500;
            $trace = Trace::fromArray($e->getTrace());
        }

        // TODO: return a response!
        throw $e;
        //dd($e->getMessage(), 'HTTP: '.$http, $trace);
    }
}
