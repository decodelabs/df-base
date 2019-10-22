<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Http\Pipeline;

use Df;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

use DecodeLabs\Glitch;

class CallableMiddleware implements MiddlewareInterface
{
    protected $callable;

    /**
     * Init with callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * Invoke the callable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = ($this->callable)($request, $handler);

        if (!$response instanceof ResponseInterface) {
            throw Glitch::EImplementation(
                'Callable middleware did not return a ResponseInterface instance'
            );
        }

        return $response;
    }
}
