<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Clause;

use Df\Opal\Query\Initiator\Select as SelectInitiator;
use Df\Opal\Query\Builder\Select as SelectBuilder;

interface IWhereFacade extends IFacade
{
    public function where(string $local, string $operator, $value): IWhereFacade;
    public function orWhere(string $local, string $operator, $value): IWhereFacade;
    public function on(string $local, string $operator, string $foreign): IWhereFacade;
    public function orOn(string $local, string $operator, string $foreign): IWhereFacade;
    public function whereField(string $local, string $operator, string $foreign): IWhereFacade;
    public function orWhereField(string $local, string $operator, string $foreign): IWhereFacade;

    public function beginWhere(callable $group=null): IWhereFacade;
    public function beginOrWhere(callable $group=null): IWhereFacade;
    public function beginOn(callable $group=null): IWhereFacade;
    public function beginOrOn(callable $group=null): IWhereFacade;

    public function whereSelect(string $local, string $operator, string $foreign): SelectInitiator;
    public function orWhereSelect(string $local, string $operator, string $foreign): SelectInitiator;
    public function whereSelectDistinct(string $local, string $operator, string $foreign): SelectInitiator;
    public function orWhereSelectDistinct(string $local, string $operator, string $foreign): SelectInitiator;

    public function whereSelectRelation(string $operator, string $local): SelectBuilder;
    public function orWhereSelectRelation(string $operator, string $local): SelectBuilder;
    public function whereSelectDistinctRelation(string $operator, string $local): SelectBuilder;
    public function orWhereSelectDistinctRelation(string $operator, string $local): SelectBuilder;

    public function addWhereClause(IWhere $clause): IWhereFacade;
    public function getWhereClauses(): array;
    public function hasWhereClauses(): bool;
    public function clearWhereClauses(): IWhereFacade;
}
