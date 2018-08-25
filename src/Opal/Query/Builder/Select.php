<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df;

use Df\Mesh\Job\ITransaction;
use Df\Mesh\Job\ITransactionAware;

use Df\Opal\Query\ISource;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

use Df\Opal\Query\IBuilder;
use Df\Opal\Query\Clause\WhereGroup;
use Df\Opal\Query\Clause\HavingGroup;

class Select implements
    IBuilder,
    IParentAware,
    ICorrelatable,
    ICorrelated,
    IDerivable,
    IExtendable,
    IJoinable,
    IWhereClauseProvider,
    IHavingClauseProvider
{
    use TSources;
    use TParentAware;
    use TRelations;
    use TCorrelatable;
    use TCorrelated;
    use TDerivable;
    use TExtendable;
    use TJoinable;
    use TWhereClauseProvider;
    use THavingClauseProvider;


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
    public function as(string $alias): IBuilder
    {
        switch ($this->getSubQueryMode()) {
            case 'correlation':
                return $this->endCorrelation($alias);

            case 'derivation':
                return $this->endDerivation($alias);

            default:
                throw Df\Error::ELogic('Query does not have a parent to be aliased into');
        }
    }


    /**
     * Complete clause subquery
     */
    public function endClause(): IBuilder
    {
        if (!$parent = $this->getParentQuery()) {
            throw Df\Error::ELogic('Query does not have a parent to be aliased into');
        }

        if (!$this->applicator) {
            throw Df\Error::ELogic('Correlated subquery does not have a clause generator applicator');
        }

        switch ($mode = $this->getSubQueryMode()) {
            case 'where':
                if (!$parent instanceof IWhereClauseProvider) {
                    throw Df\Error::ELogic('Parent query is not a where clause provider');
                }

                $parent->addWhereClause(($this->applicator)($this));
                break;

            case 'having':
                if (!$parent instanceof IHavingClauseProvider) {
                    throw Df\Error::ELogic('Parent query is not a having clause provider');
                }

                $parent->addHavingClause(($this->applicator)($this));
                break;

            default:
                throw Df\Error::ELogic('Select query is not in recognized clause mode: '.$mode);
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
}
