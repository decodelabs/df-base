<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Clause\Provider;

use Df\Opal\Query\Clause\Provider;
use Df\Opal\Query\Clause\Representation\Where as WhereRepresentation;

use Df\Opal\Query\Initiator\Select as SelectInitiator;
use Df\Opal\Query\Builder\Select as SelectBuilder;

interface Where extends Provider
{
    public function where(string $local, string $operator, $value): Where;
    public function orWhere(string $local, string $operator, $value): Where;
    public function on(string $local, string $operator, string $foreign): Where;
    public function orOn(string $local, string $operator, string $foreign): Where;
    public function whereField(string $local, string $operator, string $foreign): Where;
    public function orWhereField(string $local, string $operator, string $foreign): Where;

    public function beginWhere(callable $group=null): Where;
    public function beginOrWhere(callable $group=null): Where;
    public function beginOn(callable $group=null): Where;
    public function beginOrOn(callable $group=null): Where;

    public function whereSelect(string $local, string $operator, string $foreign): SelectInitiator;
    public function orWhereSelect(string $local, string $operator, string $foreign): SelectInitiator;
    public function whereSelectDistinct(string $local, string $operator, string $foreign): SelectInitiator;
    public function orWhereSelectDistinct(string $local, string $operator, string $foreign): SelectInitiator;

    public function whereSelectRelation(string $operator, string $local): SelectBuilder;
    public function orWhereSelectRelation(string $operator, string $local): SelectBuilder;
    public function whereSelectDistinctRelation(string $operator, string $local): SelectBuilder;
    public function orWhereSelectDistinctRelation(string $operator, string $local): SelectBuilder;

    public function addWhereClause(WhereRepresentation $clause): Where;
    public function getWhereClauses(): array;
    public function hasWhereClauses(): bool;
    public function clearWhereClauses(): Where;
}
