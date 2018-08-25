<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Clause;

use Df;
use Df\Opal\Query\Initiator\Select as SelectInitiator;
use Df\Opal\Query\Builder\Select as SelectBuilder;

interface IHavingFacade extends IFacade
{
    public function having(string $local, string $operator, $value): IHavingFacade;
    public function orHaving(string $local, string $operator, $value): IHavingFacade;
    public function havingField(string $local, string $operator, string $foreign): IHavingFacade;
    public function orHavingField(string $local, string $operator, string $foreign): IHavingFacade;
    public function beginHaving(callable $group=null): IHavingFacade;
    public function beginOrHaving(callable $group=null): IHavingFacade;

    public function havingSelect(string $local, string $operator, string $foreign): SelectInitiator;
    public function orHavingSelect(string $local, string $operator, string $foreign): SelectInitiator;
    public function havingSelectDistinct(string $local, string $operator, string $foreign): SelectInitiator;
    public function orHavingSelectDistinct(string $local, string $operator, string $foreign): SelectInitiator;

    public function havingSelectRelation(string $operator, string $local): SelectBuilder;
    public function orHavingSelectRelation(string $operator, string $local): SelectBuilder;
    public function havingSelectDistinctRelation(string $operator, string $local): SelectBuilder;
    public function orHavingSelectDistinctRelation(string $operator, string $local): SelectBuilder;

    public function addHavingClause(IHaving $clause): IHavingFacade;
    public function getHavingClauses(): array;
    public function hasHavingClauses(): bool;
    public function clearHavingClauses(): IHavingFacade;
}
