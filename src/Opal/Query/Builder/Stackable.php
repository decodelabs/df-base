<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\EntryPoint\Read as ReadEntryPoint;
use Df\Opal\Query\Builder;

interface Stackable extends
    Builder,
    ReadEntryPoint
{
    public function addStack(Stack $stack);
    public function getStacks(): array;
    public function clearStacks(): Stackable;
}
