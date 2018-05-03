<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Http\Response;

use Df;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ISender
{
    public function sendResponse(ServerRequestInterface $request, ResponseInterface $response): void;
}
