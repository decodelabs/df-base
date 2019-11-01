<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Clause\Provider;

use Df\Opal\Query\Clause\Provider;
use Df\Opal\Query\Clause\Representation\Having as HavingRepresentation;

use Df\Opal\Query\Initiator\Select as SelectInitiator;
use Df\Opal\Query\Builder\Select as SelectBuilder;

interface Having extends Provider
{
    public function having(string $local, string $operator, $value): Having;
    public function orHaving(string $local, string $operator, $value): Having;
    public function havingField(string $local, string $operator, string $foreign): Having;
    public function orHavingField(string $local, string $operator, string $foreign): Having;
    public function beginHaving(callable $group=null): Having;
    public function beginOrHaving(callable $group=null): Having;

    public function havingSelect(string $local, string $operator, string $foreign): SelectInitiator;
    public function orHavingSelect(string $local, string $operator, string $foreign): SelectInitiator;
    public function havingSelectDistinct(string $local, string $operator, string $foreign): SelectInitiator;
    public function orHavingSelectDistinct(string $local, string $operator, string $foreign): SelectInitiator;

    public function havingSelectRelation(string $operator, string $local): SelectBuilder;
    public function orHavingSelectRelation(string $operator, string $local): SelectBuilder;
    public function havingSelectDistinctRelation(string $operator, string $local): SelectBuilder;
    public function orHavingSelectDistinctRelation(string $operator, string $local): SelectBuilder;

    public function addHavingClause(HavingRepresentation $clause): Having;
    public function getHavingClauses(): array;
    public function hasHavingClauses(): bool;
    public function clearHavingClauses(): Having;
}
