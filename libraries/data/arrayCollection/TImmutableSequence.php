<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data\arrayCollection;

use df;
use df\data;

trait TImmutableSequence
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
        return false;
    }


    /**
     * Get and remove item by index
     */
    public function pull(int $key)
    {
        $key = $this->normalizeKey($key);
        return $this->items[$key] ?? null;
    }

    /**
     * Set a value by index, keys normalized
     */
    public function set(int $key, $value): data\ISequence
    {
        $output = $this->copyImmutable();
        $count = count($output->items);
        $key = min($this->normalizeKey($key), $count);

        $output->items[$key] = $value;
        return $output;
    }

    /**
     * Add an item in at selected index, move rest
     */
    public function put(int $key, $value): data\ISequence
    {
        $output = $this->copyImmutable();
        $count = count($output->items);
        $key = $this->normalizeKey($key);

        $addVals = null;

        if ($key < $count) {
            $addVals = array_splice($output->items, $key);
            $count = $key;
        }

        $output->items[] = $value;

        if ($addVals !== null) {
            $output->items = array_merge($output->items, $addVals);
        }

        return $output;
    }

    /**
     * Remove all values associated with $keys
     */
    public function remove(int ...$keys): data\ISequence
    {
        $output = $this->copyImmutable();
        $count = count($output->items);

        array_walk($keys, function (&$key) use ($count) {
            $key = $this->normalizeKey($key);
        });

        $output->items = array_values(array_diff_key($output->items, array_flip($keys)));
        return $output;
    }

    /**
     * Remove all values not associated with $keys
     */
    public function keep(int ...$keys): data\ISequence
    {
        $output = $this->copyImmutable();
        $count = count($output->items);

        array_walk($keys, function (&$key) use ($count) {
            $key = $this->normalizeKey($key);
        });

        $output->items = array_values(array_intersect_key($output->items, array_flip($keys)));
        return $output;
    }



    /**
     * Reset all values
     */
    public function clear(): data\ISequence
    {
        $output = $this->copyImmutable();
        $output->items = [];
        return $output;
    }

    /**
     * Remove all keys
     */
    public function clearKeys(): data\ISequence
    {
        $output = $this->copyImmutable();
        $output->items = array_values($output->items);
        return $output;
    }


    /**
     * Collapse multi dimensional array to flat
     */
    public function collapse(bool $unique=false, bool $removeNull=false): data\ISequence
    {
        $output = $this->copyImmutable();
        $output->items = data\Arr::collapse($output->items, false, $unique, $removeNull);
        return $output;
    }


    /**
     * Pull first item
     */
    public function pop()
    {
        return $this->getLast();
    }

    /**
     * Pull last item
     */
    public function shift()
    {
        return $this->getFirst();
    }

    /**
     * Add items to the end
     */
    public function append(...$values): data\ISequence
    {
        $output = $this->copyImmutable();
        array_push($output->items, ...$values);
        return $output;
    }

    /**
     * Add items to the start
     */
    public function prepend(...$values): data\ISequence
    {
        $output = $this->copyImmutable();
        array_unshift($output->items, ...$values);
        return $output;
    }



    /**
     * Replace all values with $value
     */
    public function fill($value): data\ISequence
    {
        $output = $this->copyImmutable();
        $output->items = array_fill_keys(array_keys($output->items), $value);
        return $output;
    }



    /**
     * Merge all passed collections into one
     */
    public function merge(iterable ...$arrays): data\ISequence
    {
        $output = $this->copyImmutable();
        $output->items = array_values(array_merge($output->items, ...data\Arr::iterablesToArrays(...$arrays)));
        return $output;
    }

    /**
     * Merge EVERYTHING :D
     */
    public function mergeRecursive(iterable ...$arrays): data\ISequence
    {
        $output = $this->copyImmutable();
        $output->items = array_values(array_merge_recursive($output->items, ...data\Arr::iterablesToArrays(...$arrays)));
        return $output;
    }


    /**
     * Like merge, but replaces.. obvs
     */
    public function replace(iterable ...$arrays): data\ISequence
    {
        $output = $this->copyImmutable();
        $output->items = array_values(array_replace($output->items, ...data\Arr::iterablesToArrays(...$arrays)));
        return $output;
    }

    /**
     * Replace EVERYTHING :D
     */
    public function replaceRecursive(iterable ...$arrays): data\ISequence
    {
        $output = $this->copyImmutable();
        $output->items = array_values(array_replace_recursive($output->items, ...data\Arr::iterablesToArrays(...$arrays)));
        return $output;
    }


    /**
     * Ensure sequence is at least $size long
     */
    public function pad(int $size, $value=null): data\ISequence
    {
        $output = $this->copyImmutable();
        $output->items = array_pad($output->items, $size, $value);
        return $output;
    }


    /**
     * Remove $offet + $length items
     */
    public function removeSlice(int $offset, int $length=null, data\ISequence &$removed=null): data\ISequence
    {
        $output = $this->copyImmutable();
        $count = count($output->items);
        $offset = $this->normalizeKey($offset);

        if ($length === null) {
            $length = $count;
        }

        $removed = new static(
            array_splice($output->items, $offset, $length)
        );

        return $output;
    }

    /**
     * Like removeSlice, but leaves a present behind
     */
    public function replaceSlice(int $offset, int $length=null, iterable $replacement, data\ISequence &$removed=null): data\ISequence
    {
        $output = $this->copyImmutable();
        $count = count($output->items);
        $offset = $this->normalizeKey($offset);

        if ($length === null) {
            $length = $count;
        }

        $removed = new static(
            array_splice($output->items, $offset, $length, array_values(data\Arr::iterableToArray($replacement)))
        );

        return $output;
    }


    /**
     * Remove duplicates from collection
     */
    public function unique(int $flags=SORT_STRING): data\ISequence
    {
        $output = $this->copyImmutable();
        $output->items = array_unique($output->items, $flags);
        return $output;
    }


    /**
     * Iterate each entry
     */
    public function walk(callable $callback, $data=null): data\ISequence
    {
        $output = $this->copyImmutable();
        array_walk($output->items, $callback, $data);
        return $output;
    }

    /**
     * Iterate everything
     */
    public function walkRecursive(callable $callback, $data=null): data\ISequence
    {
        $output = $this->copyImmutable();
        array_walk_recursive($output->items, $callback, $data);
        return $output;
    }
}
