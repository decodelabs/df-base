<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Clause;

use Df\Opal\Query\Clause;
use Df\Opal\Query\IClause;
use Df\Opal\Query\Builder\TRelations;
use Df\Opal\Query\Builder\IHavingClauseProvider;

class HavingGroup implements IHaving, IGroup, IHavingFacade
{
    use TGroup;
    use THavingFacade;
    use TRelations;


    /**
     * Init with parent
     */
    public function __construct(IFacade $parent, bool $or=false, array $clauses=null)
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
    public function clear(): IGroup
    {
        $this->having = [];
        return $this;
    }


    /**
     * Finalize clause and return parent
     */
    public function endClause(): IFacade
    {
        $parent = $this->getParent();

        if (!$parent instanceof IHavingClauseProvider) {
            throw Glitch::EUnexpectedValue('Parent query is not a having clause provider', null, $parent);
        }

        $parent->addHavingClause($this);
        return $parent;
    }
}
