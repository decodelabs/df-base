<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Arch;

use Df;

interface IRoute
{
    public function matchIn(string $method, string $path): IRoute;
    //public function matchOut(ArchUri $uri): HttpUri;
}
