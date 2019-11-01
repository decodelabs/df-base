<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Http\Response;

use Psr\Http\Message\ResponseInterface;

interface Proxy
{
    public function toHttpResponse(): ResponseInterface;
}
