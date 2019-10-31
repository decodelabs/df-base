<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\IBuilder;

use Df\Opal\Query\Initiator\Select as SelectInitiator;
use Df\Opal\Query\Initiator\Fetch as FetchInitiator;
use Df\Opal\Query\Initiator\Union as UnionInitiator;

use DecodeLabs\Glitch;

trait TStackable
{
    protected $stacks = [];

    /**
     * Start a new stacked subquery
     */
    public function select(string ...$fields): SelectInitiator
    {
        $output = new SelectInitiator($this->getSourceManager()->getApp(), $fields);
        $output->setAliasPrefix(uniqid('sss_'));
        $output->asSubQuery($this, 'stack');
        return $output;
    }

    /**
     * Start a new distinct stacked subquery
     */
    public function selectDistinct(string ...$fields): SelectInitiator
    {
        $output = new SelectInitiator($this->getSourceManager()->getApp(), $fields, true);
        $output->setAliasPrefix(uniqid('sss_'));
        $output->asSubQuery($this, 'stack');
        return $output;
    }

    /**
     *
     */
    public function union(string ...$fields): UnionInitiator
    {
        Glitch::incomplete();
    }

    /**
     *
     */
    public function fetch(): FetchInitiator
    {
        Glitch::incomplete();
    }



    /**
     * Register a stacked subquery
     */
    public function addStack(Stack $stack)
    {
        $this->stacks[$stack->getName()] = $stack;
        return $this;
    }

    /**
     * Get registered stacked subqueries
     */
    public function getStacks(): array
    {
        return $this->stacks;
    }

    /**
     * Remove stacked subquries
     */
    public function clearStacks(): IStackable
    {
        $this->stacks = [];
        return $this;
    }
}
