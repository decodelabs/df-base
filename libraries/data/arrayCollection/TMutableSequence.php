<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data\arrayCollection;

use df;
use df\data;

trait TMutableSequence
{
    use TReadable;
    use TMutableSortable;
    use TSequence {
        TSequence::__construct insteadof TReadable;
        TSequence::getKeys insteadof TReadable;
    }

    /**
     * Can the values in this collection change?
     */
    public function isMutable(): bool
    {
        return true;
    }



    /**
     * Get and remove item by index
     */
    public function pull(int $key)
    {
        $key = $this->normalizeKey($key);
        $output = $this->items[$key] ?? null;
        unset($this->items[$key]);
        return $output;
    }

    /**
     * Set a value by index, keys normalized
     */
    public function set(int $key, $value): data\ISequence
    {
        $count = count($this->items);
        $key = min($this->normalizeKey($key), $count);

        $this->items[$key] = $value;
        return $this;
    }

    /**
     * Add an item in at selected index, move rest
     */
    public function put(int $key, $value): data\ISequence
    {
        $count = count($this->items);
        $key = $this->normalizeKey($key);

        $addVals = null;

        if ($key < $count) {
            $addVals = array_splice($this->items, $key);
            $count = $key;
        }

        $this->items[] = $value;

        if ($addVals !== null) {
            $this->items = array_merge($this->items, $addVals);
        }

        return $this;
    }

    /**
     * Remove all values associated with $keys
     */
    public function remove(int ...$keys): data\ISequence
    {
        $count = count($this->items);

        array_walk($keys, function (&$key) use ($count) {
            $key = $this->normalizeKey($key);
        });

        $this->items = array_values(array_diff_key($this->items, array_flip($keys)));
        return $this;
    }

    /**
     * Remove all values not associated with $keys
     */
    public function keep(int ...$keys): data\ISequence
    {
        $count = count($this->items);

        array_walk($keys, function (&$key) use ($count) {
            $key = $this->normalizeKey($key);
        });

        $this->items = array_values(array_intersect_key($this->items, array_flip($keys)));
        return $this;
    }



    /**
     * Reset all values
     */
    public function clear(): data\ISequence
    {
        $this->items = [];
        return $this;
    }

    /**
     * Remove all keys
     */
    public function clearKeys(): data\ISequence
    {
        $this->items = array_values($this->items);
        return $this;
    }


    /**
     * Collapse multi dimensional array to flat
     */
    public function collapse(bool $unique=false, bool $removeNull=false): data\ISequence
    {
        $this->items = data\Arr::collapse($this->items, false, $unique, $removeNull);
        return $this;
    }


    /**
     * Pull first item
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * Pull last item
     */
    public function shift()
    {
        return array_shift($this->items);
    }

    /**
     * Add items to the end
     */
    public function append(...$values): data\ISequence
    {
        array_push($this->items, ...$values);
        return $this;
    }

    /**
     * Add items to the start
     */
    public function prepend(...$values): data\ISequence
    {
        array_unshift($this->items, ...$values);
        return $this;
    }



    /**
     * Replace all values with $value
     */
    public function fill($value): data\ISequence
    {
        $this->items = array_fill_keys(array_keys($this->items), $value);
        return $this;
    }


    /**
     * Merge all passed collections into one
     */
    public function merge(iterable ...$arrays): data\ISequence
    {
        $this->items = array_values(array_merge($this->items, ...data\Arr::iterablesToArrays(...$arrays)));
        return $this;
    }

    /**
     * Merge EVERYTHING :D
     */
    public function mergeRecursive(iterable ...$arrays): data\ISequence
    {
        $this->items = array_values(array_merge_recursive($this->items, ...data\Arr::iterablesToArrays(...$arrays)));
        return $this;
    }


    /**
     * Like merge, but replaces.. obvs
     */
    public function replace(iterable ...$arrays): data\ISequence
    {
        $this->items = array_values(array_replace($this->items, ...data\Arr::iterablesToArrays(...$arrays)));
        return $this;
    }

    /**
     * Replace EVERYTHING :D
     */
    public function replaceRecursive(iterable ...$arrays): data\ISequence
    {
        $this->items = array_values(array_replace_recursive($this->items, ...data\Arr::iterablesToArrays(...$arrays)));
        return $this;
    }


    /**
     * Ensure sequence is at least $size long
     */
    public function pad(int $size, $value=null): data\ISequence
    {
        $this->items = array_pad($this->items, $size, $value);
        return $this;
    }


    /**
     * Remove $offet + $length items
     */
    public function removeSlice(int $offset, int $length=null, data\ISequence &$removed=null): data\ISequence
    {
        $count = count($this->items);
        $offset = $this->normalizeKey($offset);

        if ($length === null) {
            $length = $count;
        }

        $removed = new static(
            array_splice($this->items, $offset, $length)
        );

        return $this;
    }

    /**
     * Like removeSlice, but leaves a present behind
     */
    public function replaceSlice(int $offset, int $length=null, iterable $replacement, data\ISequence &$removed=null): data\ISequence
    {
        $count = count($this->items);
        $offset = $this->normalizeKey($offset);

        if ($length === null) {
            $length = $count;
        }

        $removed = new static(
            array_splice($this->items, $offset, $length, array_values(data\Arr::iterableToArray($replacement)))
        );

        return $this;
    }


    /**
     * Remove duplicates from collection
     */
    public function unique(int $flags=SORT_STRING): data\ISequence
    {
        $this->items = array_unique($this->items, $flags);
        return $this;
    }


    /**
     * Iterate each entry
     */
    public function walk(callable $callback, $data=null): data\ISequence
    {
        array_walk($this->items, $callback, $data);
        return $this;
    }

    /**
     * Iterate everything
     */
    public function walkRecursive(callable $callback, $data=null): data\ISequence
    {
        array_walk_recursive($this->items, $callback, $data);
        return $this;
    }
}
