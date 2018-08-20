<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Plug;

use Df;

interface IContext
{
    public function __get(string $name);
}
