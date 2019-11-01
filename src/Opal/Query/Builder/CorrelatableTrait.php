<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Builder\Correlatable;
use Df\Opal\Query\Builder\Correlated;
use Df\Opal\Query\Initiator\Select as SelectInitiator;

use Df\Opal\Query\Source\Correlated as CorrelatedSource;
use Df\Opal\Query\Source\Reference;

use Df\Opal\Query\Field\Correlation as CorrelationField;

use DecodeLabs\Glitch;

trait CorrelatableTrait
{
    /**
     * Create new correlated subquery
     */
    public function correlate(string $field): SelectInitiator
    {
        $output = new SelectInitiator(
            $this->getSourceManager()->getApp(),
            [$field]
        );

        $output->setAliasPrefix(uniqid('cfs_'));
        $output->asSubQuery($this, 'correlation');
        return $output;
    }

    /**
     *  Begin a relation initiated correlated sub query
     */
    public function correlateRelation(string $relation, string $field): SelectInitiator
    {
        Glitch::incomplete();
    }


    /**
     * Shortcut to count relation correlation
     */
    public function countRelation(string $relation, string $alias=null): Correlatable
    {
        $output = $this->correlateRelation($relation, 'COUNT');

        if (!$output instanceof Correlated) {
            throw Glitch::EUnexpectedValue('Output query is not correlated', null, $output);
        }

        return $output->endCorrelation($alias);
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
    public function hasRelation(string $relation, string $alias=null): Correlatable
    {
        $output = $this->correlateRelation($relation, 'HAS');

        if (!$output instanceof Correlated) {
            throw Glitch::EUnexpectedValue('Output query is not correlated', null, $output);
        }

        return $output->endCorrelation($alias);
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
    public function addCorrelation(Select $subQuery, string $alias=null): Correlatable
    {
        $reference = new Reference(
            $source = new CorrelatedSource($subQuery, $alias ?? uniqid('cor_')),
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
