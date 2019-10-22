<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Field;

use Df\Opal\Query\Builder\ICorrelated;
use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\IField;

class Correlation implements IField
{
    protected $alias;
    protected $sourceReference;

    /**
     * Init with source, name and alias
     */
    public function __construct(Reference $sourceReference, ?string $alias=null)
    {
        $this->sourceReference = $sourceReference;

        if ($alias === null) {
            $alias = $name;
        }

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
    public function getSubQuery(): ICorrelated
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
    public function matches(IField $field): bool
    {
        if ($field->getSourceReference() !== $this->sourceReference
        || !$field instanceof Correlation) {
            return false;
        }

        return $this->getQuery() === $this->query
            && $this->getAlias() === $this->alias;
    }

    /**
     * Convert to readable string
     */
    public function __toString(): string
    {
        return '('.$this->getSubQuery().') as '.$this->alias;
    }
}
