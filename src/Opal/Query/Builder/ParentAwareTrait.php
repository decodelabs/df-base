<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Builder\ParentAware;

use Df\Opal\Query\Source;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

trait ParentAwareTrait
{
    protected $subQueryMode;
    protected $parentQuery;
    protected $applicator;

    /**
     * Use as subquery
     */
    public function asSubQuery(Builder $parent, string $mode, ?callable $applicator=null): ParentAware
    {
        return $this->setParentQuery($parent)
            ->setSubQueryMode($mode, $applicator);
    }

    /**
     * Set parent query
     */
    public function setParentQuery(?Builder $parent): ParentAware
    {
        $this->parentQuery = $parent;

        if ($parent) {
            $this->getSourceManager()->setParent(
                $parent->getSourceManager()
            );
        }

        return $this;
    }

    /**
     * Get parent query
     */
    public function getParentQuery(): ?Builder
    {
        return $this->parentQuery;
    }


    /**
     * Set sub query mode
     */
    public function setSubQueryMode(?string $mode, ?callable $applicator=null): ParentAware
    {
        $this->subQueryMode = $mode;
        $this->applicator = $applicator;
        return $this;
    }

    /**
     * Get sub query mode
     */
    public function getSubQueryMode(): ?string
    {
        return $this->subQueryMode;
    }

    /**
     * Get parent source manager if available
     */
    public function getParentSourceManager(): ?Manager
    {
        if (!$this->parentQuery) {
            return null;
        }

        return $this->parentQuery->getSourceManager();
    }

    /**
     * Get parent primary source ref if available
     */
    public function getParentPrimarySourceReference(): ?Reference
    {
        if (!$this->parentQuery) {
            return null;
        }

        return $this->parentQuery->getPrimarySourceReference();
    }

    /**
     * Get parent primary source alias if available
     */
    public function getParentPrimarySourceAlias(): ?string
    {
        if (!$this->parentQuery) {
            return null;
        }

        return $this->parentQuery->getPrimarySourceReference()->getAlias();
    }

    /**
     * Get parent primary source if available
     */
    public function getParentPrimarySource(): ?Source
    {
        if (!$this->parentQuery) {
            return null;
        }

        return $this->parentQuery->getPrimarySource();
    }


    /**
     * Is referenced source higher up the query tree?
     */
    public function isSourceDeepNested(Reference $reference): bool
    {
        if (!$this->parentQuery instanceof ParentAware) {
            return false;
        }

        $gp = $this->parentQuery;
        $sourceId = $reference->getId().' as '.$reference->getAlias();

        do {
            if (!($gp = $gp->getParentQuery())) {
                return false;
            }

            $gpReference = $gp->getPrimarySourceReference();

            if ($gpReference->getId().' as '.$gpReference->getAlias() === $sourceId) {
                return true;
            }
        } while ($gp instanceof ParentAware);

        return false;
    }
}
