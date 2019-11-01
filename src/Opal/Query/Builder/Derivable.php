<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Initiator;
use Df\Opal\Query\Builder;
use Df\Opal\Query\Builder\ParentAware;

interface Derivable extends Initiator, ParentAware
{
    public function setDerivationParent(?Initiator $parent): Derivable;
    public function getDerivationParent(): ?Initiator;

    public function endDerivation(string $alias=null): Builder;
}
