<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Clause;

use Df\Opal\Query\Initiator\Select as SelectInitiator;
use Df\Opal\Query\Builder\Select as SelectBuilder;
use Df\Opal\Query\IBuilder;

trait THavingFacade
{
    protected $having = [];

    /**
     * Create && having clause
     */
    public function having(string $local, string $operator, $value): IHavingFacade
    {
        return $this->addHavingClause(
            (new Factory($this))->createValueClause(
                $local,
                $operator,
                $value,
                false
            )
        );
    }

    /**
     * Create || having clause
     */
    public function orHaving(string $local, string $operator, $value): IHavingFacade
    {
        return $this->addHavingClause(
            (new Factory($this))->createValueClause(
                $local,
                $operator,
                $value,
                true
            )
        );
    }

    /**
     * Create && field having clause
     */
    public function havingField(string $local, string $operator, string $foreign): IHavingFacade
    {
        return $this->addHavingClause(
            (new Factory($this))->createFieldClause(
                $local,
                $operator,
                $foreign,
                false
            )
        );
    }

    /**
     * Create || field having clause
     */
    public function orHavingField(string $local, string $operator, string $foreign): IHavingFacade
    {
        return $this->addHavingClause(
            (new Factory($this))->createFieldClause(
                $local,
                $operator,
                $foreign,
                true
            )
        );
    }

    /**
     * Begin && having clause block
     */
    public function beginHaving(callable $group=null): IHavingFacade
    {
        $output = new HavingGroup($this, false);

        if ($group) {
            $group($output);
            $output = $output->endClause();

            if (!$output instanceof IHavingFacade) {
                throw Glitch::EUnexpectedValue('Parent query is not a having clause facade', null, $output);
            }
        }

        return $output;
    }

    /**
     * Begin || having clause block
     */
    public function beginOrHaving(callable $group=null): IHavingFacade
    {
        $output = new HavingGroup($this, true);

        if ($group) {
            $group($output);
            $output = $output->endClause();

            if (!$output instanceof IHavingFacade) {
                throw Glitch::EUnexpectedValue('Parent query is not a having clause facade', null, $output);
            }
        }

        return $output;
    }


    /**
     * Create && correlation based clause
     */
    public function havingSelect(string $local, string $operator, string $foreign): SelectInitiator
    {
        return $this->createHavingSelect($local, $operator, $foreign, false, false);
    }

    /**
     * Create || correlation based clause
     */
    public function orHavingSelect(string $local, string $operator, string $foreign): SelectInitiator
    {
        return $this->createHavingSelect($local, $operator, $foreign, true, false);
    }

    /**
     * Create && distinct correlation based clause
     */
    public function havingSelectDistinct(string $local, string $operator, string $foreign): SelectInitiator
    {
        return $this->createHavingSelect($local, $operator, $foreign, false, true);
    }

    /**
     * Create || distinct correlation based clause
     */
    public function orHavingSelectDistinct(string $local, string $operator, string $foreign): SelectInitiator
    {
        return $this->createHavingSelect($local, $operator, $foreign, true, true);
    }



    /**
     * Create all-in-one relation subquery clause
     */
    public function havingSelectRelation(string $operator, string $local): SelectBuilder
    {
        return $this->createRelationHavingSelect($operator, $local, false, false);
    }

    /**
     * Create || relation subquery clause
     */
    public function orHavingSelectRelation(string $operator, string $local): SelectBuilder
    {
        return $this->createRelationHavingSelect($operator, $local, true, false);
    }

    /**
     * Create && distinct relation subquery clause
     */
    public function havingSelectDistinctRelation(string $operator, string $local): SelectBuilder
    {
        return $this->createRelationHavingSelect($operator, $local, false, true);
    }

    /**
     * Create || distinct relation subquery clause
     */
    public function orHavingSelectDistinctRelation(string $operator, string $local): SelectBuilder
    {
        return $this->createRelationHavingSelect($operator, $local, true, true);
    }


    /**
     * Create select base clause
     */
    protected function createHavingSelect(string $local, string $operator, string $foreign, bool $or, bool $distinct): SelectInitiator
    {
        $output = new SelectInitiator(
            $this->getSourceManager()->getApp(),
            [$foreign],
            $distinct
        );

        $output->setAliasPrefix(uniqid('hcs_'));

        $parent = null;

        if ($this instanceof IBuilder) {
            $parent = $this;
        }
        if ($this instanceof IGroup) {
            $parent = $this->getQuery();
        }
        if (!$parent) {
            throw Glitch::EUnexpectedValue('Unable to get parent query for sub query', null, $this);
        }

        $output->asSubQuery($parent, 'having', function ($select) use ($local, $operator, $or) {
            return (new Factory($this))->createQueryClause(
                $local,
                $operator,
                $select,
                $or
            );
        });

        return $output;
    }


    /**
     * Create relation select base clause
     */
    protected function createRelationHavingSelect(string $operator, string $local, bool $or, bool $distinct): SelectBuilder
    {
        $manifest = $this->lookupRelationManifest($local);

        if ($manifest['bridgeLocal']) {
            // Double bridge correlation
            $bridge = $this->createHavingSelect($manifest['local']['field'], $operator, $manifest['bridgeLocal']['field'], $or, $distinct)
                ->from($manifest['bridgeLocal']['source'], $local);

            if (!$bridge instanceof self) {
                throw Glitch::EUnexpectedValue('Bridge source is not a where facade', null, $bridge);
            }

            $output = $bridge->createHavingSelect($manifest['bridgeForeign']['field'], 'in', $manifest['foreign']['field'], false, false)
                ->from($manifest['foreign']['source'], $local);
        } else {
            // Single correlation
            $output = $this->createHavingSelect($manifest['local']['field'], $operator, $manifest['foreign']['field'], $or, $distinct)
                ->from($manifest['foreign']['source'], $local);
        }

        if (!$output instanceof SelectBuilder) {
            throw Glitch::EUnexpectedValue('Bridge target query is not a Select', null, $output);
        }

        return $output;
    }



    /**
     * Register a having clause
     */
    public function addHavingClause(IHaving $clause): IHavingFacade
    {
        $this->having[] = $clause;
        return $this;
    }

    /**
     * Get having clause list
     */
    public function getHavingClauses(): array
    {
        return $this->having;
    }

    /**
     * Are any having clauses defined?
     */
    public function hasHavingClauses(): bool
    {
        return !empty($this->where);
    }

    /**
     * Remove all having clauses
     */
    public function clearHavingClauses(): IHavingFacade
    {
        $this->having = [];
        return $this;
    }
}
