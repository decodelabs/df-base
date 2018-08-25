<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Clause;

use Df;
use Df\Data\IArrayProvider;

use Df\Opal\Query\IBuilder;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

interface IGroup extends IRepresentation, IFacade, IArrayProvider, \Countable
{
    public function getParent(): IFacade;
    public function getQuery(): IBuilder;

    public function isEmpty(): bool;
    public function clear(): IGroup;

    public function endClause(): IFacade;
}
