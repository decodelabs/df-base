<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data\arrayCollection;

use df;
use df\data;

trait TCollection
{
    protected $items = [];

    /**
     * Direct set items
     */
    public function __construct(iterable $items)
    {
        $this->items = data\Arr::iterableToArray($items);
    }

    /**
     * Is array empty?
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }


    /**
     * Check $this is mutable and copy if needed
     */
    public function ensureMutable(): data\ICollection
    {
        if ($this->isMutable()) {
            return $this;
        } else {
            return $this->copyMutable();
        }
    }

    /**
     * Check $this is immutable and copy if needed
     */
    public function ensureImmutable(): data\ICollection
    {
        if (!$this->isMutable()) {
            return $this;
        } else {
            return $this->copyImmutable();
        }
    }

    /**
     * Duplicate collection, can change type if needed
     */
    public function copy(): data\ICollection
    {
        if ($this->isMutable()) {
            return $this->copyMutable();
        } else {
            return $this->copyImmutable();
        }
    }


    /**
     * Iterator interface
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return $this->items;
    }



    /**
     * Get dump info
     */
    public function __debugInfo(): array
    {
        return $this->items;
    }
}
