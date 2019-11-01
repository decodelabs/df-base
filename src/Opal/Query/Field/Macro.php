<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Field;

use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\Field;

class Macro implements Field
{
    protected $type;
    protected $inner;
    protected $alias;
    protected $sourceReference;

    /**
     * Init with source, name and alias
     */
    public function __construct(Reference $sourceReference, string $type, string $inner, string $alias)
    {
        $this->type = $type;
        $this->inner = $inner;
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
        || !$field instanceof Macro) {
            return false;
        }

        return $field->type === $this->type
            && $field->inner === $this->inner
            && $field->alias === $this->alias;
    }

    /**
     * Convert to readable string
     */
    public function __toString(): string
    {
        return $this->type.'('.$this->inner.') as '.$this->alias;
    }
}
