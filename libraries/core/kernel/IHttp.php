<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\core\kernel;

use df;
use df\core;

use df\http\response\ISender;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface IHttp extends RequestHandlerInterface, ISender
{
    public function run(): void;
    public function prepareServerRequest(): ServerRequestInterface;
    public function terminate(ServerRequestInterface $request, ResponseInterface $response): void;
}
