<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;

interface Nestable extends Builder
{
    public function nest(string ...$fields): Nest;
    public function addNest(Nest $nest): Nestable;
    public function getNests(): array;
    public function clearNests(): Nestable;
}
