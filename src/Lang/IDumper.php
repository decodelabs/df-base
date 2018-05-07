<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Lang;

use Df;

interface IDumper
{
    public function dumpOne($var): void;
    public function dump(...$var): void;
    public function dumpDie(...$vars): void;
}
