<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Clause;

use Df\Opal\Query\Clause;
use Df\Opal\Query\IClause;

class WhereGroup implements IWhere, IGroup, IWhereFacade
{
    use TGroup;
    use TWhereFacade;

    protected $prerequisiteName = null;


    /**
     * Init with parent
     */
    public function __construct(IFacade $parent, bool $or=false, array $clauses=null)
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
    public function setPrerequisiteName(?string $name): WhereGroup
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
    public function endPrerequisite(string $name=null): IWhereFacade
    {
        if ($name !== null) {
            $this->prerequisiteName = $name;
        }

        if ($this->prerequisiteName === null) {
            $this->prerequisiteName = uniqid('prq_');
        }

        $parent = $this->getParent();
        $parent->addPrerequisite($this);

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
    public function clear(): IGroup
    {
        $this->where = [];
        return $this;
    }


    /**
     * Finalize clause and return parent
     */
    public function endClause(): IFacade
    {
        $parent = $this->getParent();
        $parent->addWhereClause($this);

        return $parent;
    }
}
