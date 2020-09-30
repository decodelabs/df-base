<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Clause\Group;

use Df\Opal\Query\Clause\Provider;
use Df\Opal\Query\Clause\Provider\Where as WhereClauseProvider;
use Df\Opal\Query\Clause\Provider\WhereTrait as WhereClauseProviderTrait;
use Df\Opal\Query\Clause\Representation\Where as WhereRepresentation;
use Df\Opal\Query\Clause\Group;
use Df\Opal\Query\Clause\GroupTrait;

use Df\Opal\Query\Builder\RelationInspectorTrait;
use Df\Opal\Query\Builder\WhereClauseProvider as WhereClauseProviderBuilder;

use DecodeLabs\Exceptional;

class Where implements WhereRepresentation, Group, WhereClauseProvider
{
    use GroupTrait;
    use WhereClauseProviderTrait;
    use RelationInspectorTrait;

    protected $prerequisiteName = null;


    /**
     * Init with parent
     */
    public function __construct(Provider $parent, bool $or=false, array $clauses=null)
    {
        $this->parent = $parent;
        $this->setOr($or);

        if ($clauses) {
            foreach ($clauses as $clause) {
                $this->addWhereClause($clause);
            }
        }
    }


    /**
     * Set prerequisite name
     */
    public function setPrerequisiteName(?string $name): Where
    {
        $this->prerequisiteName = $name;
        return $this;
    }

    /**
     * Get prerequisite name
     */
    public function getPrerequisiteName(): ?string
    {
        return $this->prerequisiteName;
    }

    /**
     * End prerequisite
     */
    public function endPrerequisite(string $name=null): WhereClauseProvider
    {
        if ($name !== null) {
            $this->prerequisiteName = $name;
        }

        if ($this->prerequisiteName === null) {
            $this->prerequisiteName = uniqid('prq_');
        }

        $parent = $this->getParent();

        if (!$parent instanceof WhereClauseProviderBuilder) {
            throw Exceptional::UnexpectedValue(
                'Parent query is not a where clause provider', null, $parent
            );
        }

        $parent->addPrerequisite($this->prerequisiteName, $this);
        return $parent;
    }


    /**
     * Are there any children?
     */
    public function isEmpty(): bool
    {
        return !empty($this->where);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return $this->where;
    }

    /**
     * Count children
     */
    public function count(): int
    {
        return count($this->where);
    }

    /**
     * Clear child clauses
     */
    public function clear(): Group
    {
        $this->where = [];
        return $this;
    }


    /**
     * Finalize clause and return parent
     */
    public function endClause(): Provider
    {
        $parent = $this->getParent();

        if (!$parent instanceof WhereClauseProviderBuilder) {
            throw Exceptional::UnexpectedValue(
                'Parent query is not a where clause provider', null, $parent
            );
        }

        $parent->addWhereClause($this);
        return $parent;
    }
}
