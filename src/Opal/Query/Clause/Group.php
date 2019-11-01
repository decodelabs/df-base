<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Clause;

use Df\Opal\Query\Clause\Representation;
use Df\Opal\Query\Clause\Provider;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

use DecodeLabs\Collections\ArrayProvider;

interface Group extends Representation, Provider, ArrayProvider, \Countable
{
    public function getParent(): Provider;
    public function getQuery(): Builder;

    public function isEmpty(): bool;
    public function clear(): Group;

    public function endClause(): Provider;
}
