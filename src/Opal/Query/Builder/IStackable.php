<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df;
use Df\Opal\Query\IReadFacade;
use Df\Opal\Query\IBuilder;

interface IStackable extends
    IBuilder,
    IReadFacade
{
    public function addStack(Stack $stack);
    public function getStacks(): array;
    public function clearStacks(): IStackable;
}
