<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Builder\Joinable;
use Df\Opal\Query\Initiator\Join as JoinInitiator;

use DecodeLabs\Glitch;

trait JoinableTrait
{
    protected $joins = [];

    /**
     * Begin a direct inner join
     */
    public function join(string ...$fields): JoinInitiator
    {
        return $this->createJoin($fields, 'inner');
    }

    /**
     * All-in-one relation based inner join
     */
    public function joinRelation(string $relation, string ...$fields): Joinable
    {
        $this->beginJoinRelation($relation, ...$fields)->endJoin();
        return $this;
    }

    /**
     * Begin relation based inner join
     */
    public function beginJoinRelation(string $relation, string ...$fields): Join
    {
        return $this->createJoinRelation($relation, $fields, 'inner');
    }


    /**
     * Begin a direct left join
     */
    public function leftJoin(string ...$fields): JoinInitiator
    {
        return $this->createJoin($fields, 'left');
    }

    /**
     * All-in-one relation based left join
     */
    public function leftJoinRelation(string $relation, string ...$fields): Joinable
    {
        $this->beginLeftJoinRelation($relation, ...$fields)->endJoin();
        return $this;
    }

    /**
     * Begin relation based left join
     */
    public function beginLeftJoinRelation(string $relation, string ...$fields): Join
    {
        return $this->createJoinRelation($relation, $fields, 'left');
    }


    /**
     * Begin a direct right join
     */
    public function rightJoin(string ...$fields): JoinInitiator
    {
        return $this->createJoin($fields, 'right');
    }

    /**
     * All-in-one relation based right join
     */
    public function rightJoinRelation(string $relation, string ...$fields): Joinable
    {
        $this->beginRightJoinRelation($relation, ...$fields)->endJoin();
        return $this;
    }

    /**
     * Begin relation based right join
     */
    public function beginRightJoinRelation(string $relation, string ...$fields): Join
    {
        return $this->createJoinRelation($relation, $fields, 'right');
    }


    /**
     * Begin a direct outer join
     */
    public function outerJoin(string ...$fields): JoinInitiator
    {
        return $this->createJoin($fields, 'outer');
    }

    /**
     * All-in-one relation based right join
     */
    public function outerJoinRelation(string $relation, string ...$fields): Joinable
    {
        $this->beginOuterJoinRelation($relation, ...$fields)->endJoin();
        return $this;
    }

    /**
     * Begin relation based outer join
     */
    public function beginOuterJoinRelation(string $relation, string ...$fields): Join
    {
        return $this->createJoinRelation($relation, $fields, 'outer');
    }



    /**
     * Initiate a join query
     */
    protected function createJoin(array $fields, string $type): JoinInitiator
    {
        return (new JoinInitiator(
            $this,
            $fields,
            $type
        ));
    }

    /**
     * Initiate a relation join query
     */
    protected function createJoinRelation(string $relation, array $fields, string $type): Join
    {
        Glitch::incomplete();
    }




    /**
     * Register a join query block
     */
    public function addJoin(Join $join, string $alias=null): Joinable
    {
        $this->joins[$alias ?? $join->getSourceAlias()] = $join;
        return $this;
    }

    /**
     * Get list of registered joins
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * Remove all joins
     */
    public function clearJoins(): Joinable
    {
        $manager = $this->getSourceManager();

        foreach ($this->joins as $join) {
            $manager->removeSource($join->getSourceReference()->getSource());
        }

        $this->joins = [];
        return $this;
    }
}
