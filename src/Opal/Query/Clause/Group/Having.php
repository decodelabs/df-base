<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Clause\Group;

use Df\Opal\Query\Clause\Provider;
use Df\Opal\Query\Clause\Provider\Having as HavingClauseProvider;
use Df\Opal\Query\Clause\Provider\HavingTrait as HavingClauseProviderTrait;
use Df\Opal\Query\Clause\Representation\Having as HavingRepresentation;
use Df\Opal\Query\Clause\Group;
use Df\Opal\Query\Clause\GroupTrait;

use Df\Opal\Query\Builder\RelationInspectorTrait;
use Df\Opal\Query\Builder\HavingClauseProvider as HavingClauseProviderBuilder;

class Having implements HavingRepresentation, Group, HavingClauseProvider
{
    use GroupTrait;
    use HavingClauseProviderTrait;
    use RelationInspectorTrait;


    /**
     * Init with parent
     */
    public function __construct(Provider $parent, bool $or=false, array $clauses=null)
    {
        $this->parent = $parent;
        $this->setOr($or);

        if ($clauses) {
            foreach ($clauses as $clause) {
                $this->addHavingClause($clause);
            }
        }
    }

    /**
     * Are there any children?
     */
    public function isEmpty(): bool
    {
        return !empty($this->having);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return $this->having;
    }

    /**
     * Count children
     */
    public function count(): int
    {
        return count($this->having);
    }

    /**
     * Clear child clauses
     */
    public function clear(): Group
    {
        $this->having = [];
        return $this;
    }


    /**
     * Finalize clause and return parent
     */
    public function endClause(): Provider
    {
        $parent = $this->getParent();

        if (!$parent instanceof HavingClauseProviderBuilder) {
            throw Glitch::EUnexpectedValue('Parent query is not a having clause provider', null, $parent);
        }

        $parent->addHavingClause($this);
        return $parent;
    }
}
