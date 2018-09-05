<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df;
use Df\Opal\Query\Source\Correlated as CorrelatedSource;
use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\Field\Correlation as CorrelationField;
use Df\Opal\Query\IBuilder;
use Df\Opal\Query\Initiator\Select as SelectInitiator;

trait TCorrelatable
{
    /**
     * Create new correlated subquery
     */
    public function correlate(string $field): SelectInitiator
    {
        return (new SelectInitiator(
                $this->getSourceManager()->getApp(),
                [$field]
            ))
            ->setAliasPrefix(uniqid('cfs_'))
            ->asSubQuery($this, 'correlation');
    }

    /**
     *  Begin a relation initiated correlated sub query
     */
    public function correlateRelation(string $relation, string $field): SelectInitiator
    {
        Df\incomplete();
    }


    /**
     * Shortcut to count relation correlation
     */
    public function countRelation(string $relation, string $alias=null): ICorrelatable
    {
        return $this->correlateRelation($relation, 'COUNT')
            ->endCorrelation($alias);
    }

    /**
     * Being a count relation subquery
     */
    public function beginCountRelation(string $relation): SelectInitiator
    {
        return $this->correlateRelation($relation, 'COUNT');
    }

    /**
     * Shortcut to has relation correlation
     */
    public function hasRelation(string $relation, string $alias=null): ICorrelatable
    {
        return $this->correlateRelation($relation, 'HAS')
            ->endCorrelation($alias);
    }

    /**
     * Being a has relation subquery
     */
    public function beginHasRelation(string $relation): SelectInitiator
    {
        return $this->correlateRelation($relation, 'HAS');
    }


    /**
     * Register a correlated subquery
     */
    public function addCorrelation(Select $subQuery, string $alias=null): ICorrelatable
    {
        $reference = new Reference(
            $source = new CorrelatedSource($subQuery, $alias),
            $alias,
            $subQuery->getPrimarySourceReference()->getPrefix()
        );

        $field = new CorrelationField($reference, $alias);
        $reference->registerField($field);

        $this->getSourceManager()->addReference($reference);
        return $this;
    }

    /**
     * Extract list of correlation queries from field list
     */
    public function getCorrelations(): array
    {
        $output = [];

        foreach ($this->getPrimarySourceReference()->getFields() as $field) {
            if ($field instanceof CorrelationField) {
                $output[$field->getAlias()] = $field;
            }
        }

        return $output;
    }
}
