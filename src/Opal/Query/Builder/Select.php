<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Source;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Builder\SourceProviderTrait;
use Df\Opal\Query\Builder\ParentAware;
use Df\Opal\Query\Builder\ParentAwareTrait;
use Df\Opal\Query\Builder\RelationInspectorTrait;
use Df\Opal\Query\Builder\Correlatable;
use Df\Opal\Query\Builder\CorrelatableTrait;
use Df\Opal\Query\Builder\Correlated;
use Df\Opal\Query\Builder\CorrelatedTrait;
use Df\Opal\Query\Builder\Derivable;
use Df\Opal\Query\Builder\DerivableTrait;
use Df\Opal\Query\Builder\Extendable;
use Df\Opal\Query\Builder\ExtendableTrait;
use Df\Opal\Query\Builder\Joinable;
use Df\Opal\Query\Builder\JoinableTrait;
use Df\Opal\Query\Builder\WhereClauseProvider;
use Df\Opal\Query\Builder\WhereClauseProviderTrait;
use Df\Opal\Query\Builder\HavingClauseProvider;
use Df\Opal\Query\Builder\HavingClauseProviderTrait;
use Df\Opal\Query\Builder\Stackable;
use Df\Opal\Query\Builder\StackableTrait;
use Df\Opal\Query\Builder\StackedData;
use Df\Opal\Query\Builder\StackedDataTrait;
use Df\Opal\Query\Builder\Nestable;
use Df\Opal\Query\Builder\NestableTrait;

use Df\Opal\Query\Clause\Group\Where as WhereGroup;
use Df\Opal\Query\Clause\Group\Having as HavingGroup;

use DecodeLabs\Glitch\Dumpable;
use DecodeLabs\Exceptional;

class Select implements
    Builder,
    ParentAware,
    Correlatable,
    Correlated,
    Derivable,
    Extendable,
    Joinable,
    WhereClauseProvider,
    HavingClauseProvider,

    Stackable,
    StackedData,
    Nestable,

    Dumpable
{
    use SourceProviderTrait;
    use ParentAwareTrait;
    use RelationInspectorTrait;
    use CorrelatableTrait;
    use CorrelatedTrait;
    use DerivableTrait;
    use ExtendableTrait;
    use JoinableTrait;
    use WhereClauseProviderTrait;
    use HavingClauseProviderTrait;

    use StackableTrait;
    use StackedDataTrait;
    use NestableTrait;


    protected $distinct = false;
    protected $sourceManager;
    protected $sourceReference;

    /**
     * Init with manager and reference
     */
    public function __construct(Manager $manager, Reference $reference)
    {
        $this->sourceManager = $manager;
        $this->sourceReference = $reference;
    }

    /**
     * Get source manager
     */
    public function getSourceManager(): Manager
    {
        return $this->sourceManager;
    }

    /**
     * Get main source ref
     */
    public function getPrimarySourceReference(): Reference
    {
        return $this->sourceReference;
    }



    /**
     * Set as disctinct
     */
    public function setDistinct(bool $distinct): Select
    {
        $this->distinct = $distinct;
        return $this;
    }

    /**
     * Is distinct
     */
    public function isDistinct(): bool
    {
        return $this->distinct;
    }


    /**
     * Complete subquery and return parent
     */
    public function as(string $alias): Builder
    {
        switch ($this->getSubQueryMode()) {
            case 'correlation':
                return $this->endCorrelation($alias);

            case 'derivation':
                return $this->endDerivation($alias);

            case 'stack':
                return $this->asMany($alias);

            default:
                throw Exceptional::Logic(
                    'Query does not have a parent to be aliased into'
                );
        }
    }


    /**
     * Complete clause subquery
     */
    public function endClause(): Builder
    {
        if (!$parent = $this->getParentQuery()) {
            throw Exceptional::Logic(
                'Query does not have a parent to be aliased into'
            );
        }

        if (!$this->applicator) {
            throw Exceptional::Logic(
                'Correlated subquery does not have a clause generator applicator'
            );
        }

        switch ($mode = $this->getSubQueryMode()) {
            case 'where':
                if (!$parent instanceof WhereClauseProvider) {
                    throw Exceptional::Logic(
                        'Parent query is not a where clause provider'
                    );
                }

                $parent->addWhereClause(($this->applicator)($this));
                break;

            case 'having':
                if (!$parent instanceof HavingClauseProvider) {
                    throw Exceptional::Logic(
                        'Parent query is not a having clause provider'
                    );
                }

                $parent->addHavingClause(($this->applicator)($this));
                break;

            default:
                throw Exceptional::Logic(
                    'Select query is not in recognized clause mode: '.$mode
                );
        }


        return $parent;
    }



    /**
     * Render as pseudo SQL
     */
    public function __toString(): string
    {
        $output = 'SELECT '."\n";
        $fields = [];

        foreach ($this->sourceManager->getReferences() as $reference) {
            foreach ($reference->getFields() as $field) {
                $fields[] = str_replace("\n", "\n    ", (string)$field);
            }
        }

        $output .= '    '.implode("\n    ", $fields)."\n";
        $output .= '  FROM '.$this->sourceReference."\n";

        foreach ($this->joins as $join) {
            $output .= '  '.str_replace("\n", "\n  ", (string)$join)."\n";
        }

        if (!empty($where = $this->getWhereClauses())) {
            $group = new WhereGroup($this, false, $where);
            $output .= '  WHERE '.$group."\n";
        }

        if (!empty($having = $this->getHavingClauses())) {
            $group = new HavingGroup($this, false, $having);
            $output .= '  HAVING '.$group."\n";
        }

        foreach ($this->getStacks() as $stack) {
            $output .= '  '.str_replace("\n", "\n  ", (string)$stack)."\n";
        }

        foreach ($this->getNests() as $nest) {
            $output .= '  '.str_replace("\n", "\n  ", (string)$nest)."\n";
        }

        return $output;
    }


    /**
     * Debug info
     */
    public function __debugInfo(): array
    {
        return [
            'sourceManager' => $this->sourceManager,
            'source' => $this->sourceReference,
            'sql' => $this->__toString()
        ];
    }


    /**
     * Export for dump inspection
     */
    public function glitchDump(): iterable
    {
        yield 'text' => $this->__toString();
        yield '^property:*sourceManager' => $this->sourceManager;
        yield '^property:%source' => $this->sourceReference;
    }
}
