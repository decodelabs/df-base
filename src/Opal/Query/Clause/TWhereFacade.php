<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Clause;

use Df;
use Df\Opal\Query\Initiator\Select as SelectInitiator;
use Df\Opal\Query\Builder\Select as SelectBuilder;

use Df\Opal\Query\Clause;
use Df\Opal\Query\Clause\Factory;

trait TWhereFacade
{
    protected $where = [];

    /**
     * Create && where clause
     */
    public function where(string $local, string $operator, $value): IWhereFacade
    {
        return $this->addWhereClause(
            (new Factory($this))->createValueClause(
                $local,
                $operator,
                $value,
                false
            )
        );
    }

    /**
     * Create || where clause
     */
    public function orWhere(string $local, string $operator, $value): IWhereFacade
    {
        return $this->addWhereClause(
            (new Factory($this))->createValueClause(
                $local,
                $operator,
                $value,
                true
            )
        );
    }

    /**
     * Alias whereField
     */
    public function on(string $local, string $operator, string $foreign): IWhereFacade
    {
        return $this->whereField($local, $operator, $foreign);
    }

    /**
     * Alias orWhereField
     */
    public function orOn(string $local, string $operator, string $foreign): IWhereFacade
    {
        return $this->orWhereField($local, $operator, $foreign);
    }

    /**
     * Create && field where clause
     */
    public function whereField(string $local, string $operator, string $foreign): IWhereFacade
    {
        return $this->addWhereClause(
            (new Factory($this))->createFieldClause(
                $local,
                $operator,
                $foreign,
                false
            )
        );
    }

    /**
     * Create || field where clause
     */
    public function orWhereField(string $local, string $operator, string $foreign): IWhereFacade
    {
        return $this->addWhereClause(
            (new Factory($this))->createFieldClause(
                $local,
                $operator,
                $foreign,
                true
            )
        );
    }

    /**
     * Begin && where clause block
     */
    public function beginWhere(callable $group=null): IWhereFacade
    {
        $output = new WhereGroup($this, false);

        if ($group) {
            $group($output);
            return $output->endClause();
        } else {
            return $output;
        }
    }

    /**
     * Begin || where clause block
     */
    public function beginOrWhere(callable $group=null): IWhereFacade
    {
        $output = new WhereGroup($this, true);

        if ($group) {
            $group($output);
            return $output->endClause();
        } else {
            return $output;
        }
    }



    /**
     * Alias beginWhere
     */
    public function beginOn(callable $group=null): IWhereFacade
    {
        return $this->beginWhere($group);
    }

    /**
     * Alias beginOrWhere
     */
    public function beginOrOn(callable $group=null): IWhereFacade
    {
        return $this->beginOrWhere($group);
    }


    /**
     * Create && correlation based clause
     */
    public function whereSelect(string $local, string $operator, string $foreign): SelectInitiator
    {
        return $this->createWhereSelect($local, $operator, $foreign, false, false);
    }

    /**
     * Create || correlation based clause
     */
    public function orWhereSelect(string $local, string $operator, string $foreign): SelectInitiator
    {
        return $this->createWhereSelect($local, $operator, $foreign, true, false);
    }

    /**
     * Create && distinct correlation based clause
     */
    public function whereSelectDistinct(string $local, string $operator, string $foreign): SelectInitiator
    {
        return $this->createWhereSelect($local, $operator, $foreign, false, true);
    }

    /**
     * Create || distinct correlation based clause
     */
    public function orWhereSelectDistinct(string $local, string $operator, string $foreign): SelectInitiator
    {
        return $this->createWhereSelect($local, $operator, $foreign, true, true);
    }




    /**
     * Create && relation subquery clause
     */
    public function whereSelectRelation(string $operator, string $local): SelectBuilder
    {
        return $this->createRelationWhereSelect($operator, $local, false, false);
    }

    /**
     * Create || relation subquery clause
     */
    public function orWhereSelectRelation(string $operator, string $local): SelectBuilder
    {
        return $this->createRelationWhereSelect($operator, $local, true, false);
    }

    /**
     * Create && distinct relation subquery clause
     */
    public function whereSelectDistinctRelation(string $operator, string $local): SelectBuilder
    {
        return $this->createRelationWhereSelect($operator, $local, false, true);
    }

    /**
     * Create || distinct relation subquery clause
     */
    public function orWhereSelectDistinctRelation(string $operator, string $local): SelectBuilder
    {
        return $this->createRelationWhereSelect($operator, $local, true, true);
    }



    /**
     * Create select base clause
     */
    protected function createWhereSelect(string $local, string $operator, string $foreign, bool $or, bool $distinct): SelectInitiator
    {
        return (new SelectInitiator(
                $this->getSourceManager()->getApp(),
                [$foreign],
                $distinct
            ))
            ->setAliasPrefix(uniqid('wcs_'))
            ->asSubQuery($this, 'where', function ($select) use ($local, $operator, $or) {
                return (new Factory($this))->createQueryClause(
                    $local,
                    $operator,
                    $select,
                    $or
                );
            });
    }

    /**
     * Create relation select base clause
     */
    protected function createRelationWhereSelect(string $operator, string $local, bool $or, bool $distinct): SelectBuilder
    {
        $manifest = $this->lookupRelationManifest($local);

        if ($manifest['bridgeLocal']) {
            // Double bridge correlation
            return $this->createWhereSelect($manifest['local']['field'], $operator, $manifest['bridgeLocal']['field'], $or, $distinct)
                ->from($manifest['bridgeLocal']['source'], $local)
                ->createWhereSelect($manifest['bridgeForeign']['field'], 'in', $manifest['foreign']['field'])
                    ->from($manifest['foreign']['source'], $local);
        } else {
            // Single correlation
            return $this->createWhereSelect($manifest['local']['field'], $operator, $manifest['foreign']['field'], $or, $distinct)
                ->from($manifest['foreign']['source'], $local);
        }
    }



    /**
     * Register a where clause
     */
    public function addWhereClause(IWhere $clause): IWhereFacade
    {
        $this->where[] = $clause;
        return $this;
    }

    /**
     * Get where clause list
     */
    public function getWhereClauses(): array
    {
        return $this->where;
    }

    /**
     * Are any where clauses defined?
     */
    public function hasWhereClauses(): bool
    {
        return !empty($this->where);
    }

    /**
     * Remove all where clauses
     */
    public function clearWhereClauses(): IWhereFacade
    {
        $this->where = [];
        return $this;
    }
}
