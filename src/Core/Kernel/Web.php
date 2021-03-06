<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Kernel;

use Df\Http\Response\Sender;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface Web extends RequestHandlerInterface, Sender
{
    public function run(): void;
    public function prepareRequest(): ServerRequestInterface;
    public function terminate(ServerRequestInterface $request, ResponseInterface $response): void;
}
