<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\IBuilder;
use Df\Opal\Query\IInitiator;

interface IDerivable extends IInitiator, IParentAware
{
    public function setDerivationParent(?IInitiator $parent): IDerivable;
    public function getDerivationParent(): ?IInitiator;

    public function endDerivation(string $alias=null): IBuilder;
}
