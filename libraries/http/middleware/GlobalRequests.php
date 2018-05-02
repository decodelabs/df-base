<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\http\middleware;

use df;
use df\http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class GlobalRequests implements MiddlewareInterface
{
    /**
     * Invoke the callable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'OPTIONS' && $request->getRequestTarget() === '*') {
            return new http\response\Text('', 200, [
                'allow' => 'OPTIONS,GET,HEAD,POST,PUT,DELETE'
            ]);
        }

        return $handler->handle($request);
    }
}
