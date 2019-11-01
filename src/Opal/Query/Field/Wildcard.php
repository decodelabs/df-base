<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Field;

use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\Field;

class Wildcard implements Field
{
    protected $sourceReference;

    /**
     * Init with source, name and alias
     */
    public function __construct(Reference $sourceReference)
    {
        $this->sourceReference = $sourceReference;
    }

    /**
     * Get alias
     */
    public function getAlias(): string
    {
        return '*';
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
        if ($field->getSourceReference() !== $this->sourceReference) {
            return false;
        }

        return $field instanceof Wildcard;
    }

    /**
     * Convert to readable string
     */
    public function __toString(): string
    {
        return $this->sourceReference->getPrefixedAlias().'.*';
    }
}
