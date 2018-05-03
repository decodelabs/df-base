<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data\arrayCollection;

use df;

use df\data\Arr;
use df\data\ICollection;

trait TCollection
{
    //const MUTABLE = false;

    protected $items = [];

    /**
     * Direct set items
     */
    public function __construct(iterable $items)
    {
        $this->items = Arr::iterableToArray($items);
    }


    /**
     * Can the values in this collection change?
     */
    public function isMutable(): bool
    {
        return static::MUTABLE;
    }

    /**
     * Is array empty?
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }


    /**
     * Duplicate collection, can change type if needed
     */
    public function copy(): ICollection
    {
        return clone $this;
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
     * Convert to json
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }



    /**
     * Get dump info
     */
    public function __debugInfo(): array
    {
        return $this->items;
    }
}
