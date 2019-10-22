<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Field;

use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\IField;

class Intrinsic implements IField, INamed
{
    protected $name;
    protected $alias;
    protected $sourceReference;

    /**
     * Init with source, name and alias
     */
    public function __construct(Reference $sourceReference, string $name, ?string $alias=null)
    {
        $this->name = $name;
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
     * Get name
     */
    public function getName(): string
    {
        return $this->name;
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
        || !$field instanceof Intrinsic) {
            return false;
        }

        return $field->getName() === $this->name
            && $field->getAlias() === $this->alias;
    }

    /**
     * Convert to readable string
     */
    public function __toString(): string
    {
        return $this->sourceReference->getPrefixedAlias().'.'.$this->name.' as '.$this->alias;
    }
}
