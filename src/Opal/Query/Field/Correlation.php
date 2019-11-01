<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Field;

use Df\Opal\Query\Builder\Correlated;
use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\Field;

class Correlation implements Field
{
    protected $alias;
    protected $sourceReference;

    /**
     * Init with source, name and alias
     */
    public function __construct(Reference $sourceReference, ?string $alias=null)
    {
        $this->sourceReference = $sourceReference;
        $this->alias = $alias;
    }

    /**
     * Get alias
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * Get query
     */
    public function getSubQuery(): Correlated
    {
        return $this->sourceReference->getSource()->getSubQuery();
    }

    /**
     * Get source reference
     */
    public function getSourceReference(): Reference
    {
        return $this->sourceReference;
    }

    /**
     * Does field match?
     */
    public function matches(Field $field): bool
    {
        if ($field->getSourceReference() !== $this->sourceReference
        || !$field instanceof Correlation) {
            return false;
        }

        return $field->getSubQuery() === $this->getSubQuery()
            && $field->getAlias() === $this->alias;
    }

    /**
     * Convert to readable string
     */
    public function __toString(): string
    {
        return '('.(string)$this->getSubQuery().') as '.$this->alias;
    }
}
