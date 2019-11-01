<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Initiator\Join as JoinInitiator;

interface Joinable extends Builder
{
    // Inner
    public function join(string ...$fields): JoinInitiator;
    public function joinRelation(string $relation, string ...$fields): Joinable;
    public function beginJoinRelation(string $relation, string ...$fields): Join;

    // Left
    public function leftJoin(string ...$fields): JoinInitiator;
    public function leftJoinRelation(string $relation, string ...$fields): Joinable;
    public function beginLeftJoinRelation(string $relation, string ...$fields): Join;

    // Right
    public function rightJoin(string ...$fields): JoinInitiator;
    public function rightJoinRelation(string $relation, string ...$fields): Joinable;
    public function beginRightJoinRelation(string $relation, string ...$fields): Join;

    // Outer
    public function outerJoin(string ...$fields): JoinInitiator;
    public function outerJoinRelation(string $relation, string ...$fields): Joinable;
    public function beginOuterJoinRelation(string $relation, string ...$fields): Join;

    public function addJoin(Join $join, string $alias=null): Joinable;
    public function getJoins(): array;
    public function clearJoins(): Joinable;
}
