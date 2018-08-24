<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df;
use Df\Opal\Query\IBuilder;
use Df\Opal\Query\ISource;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

trait TParentAware
{
    protected $subQueryMode;
    protected $parentQuery;

    /**
     * Use as subquery
     */
    public function asSubQuery(IBuilder $parent, string $mode): IParentAware
    {
        return $this->setParentQuery($parent)
            ->setSubQueryMode($mode);
    }

    /**
     * Set parent query
     */
    public function setParentQuery(?IBuilder $parent): IParentAware
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
    public function getParentQuery(): ?IBuilder
    {
        return $this->parentQuery;
    }


    /**
     * Set sub query mode
     */
    public function setSubQueryMode(?string $mode): IParentAware
    {
        $this->subQueryMode = $mode;
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
    public function getParentPrimarySource(): ?ISource
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
        if (!$this->parent instanceof IParentAware) {
            return false;
        }

        $gp = $this->parent;
        $sourceId = $reference->getId().' as '.$reference->getAlias();

        do {
            $gp = $parent->getParentQuery();
            $gpReference = $gp->getPrimarySourceReference();

            if ($gpReference->getId().' as '.$gpReference->getAlias() === $sourceId) {
                return true;
            }
        } while ($gp instanceof IParentAware);

        return false;
    }
}
