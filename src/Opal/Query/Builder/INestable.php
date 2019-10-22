<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\IBuilder;

interface INestable extends IBuilder
{
    public function nest(string ...$fields): Nest;
    public function addNest(Nest $nest): INestable;
    public function getNests(): array;
    public function clearNests(): INestable;
}
