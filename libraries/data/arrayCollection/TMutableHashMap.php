<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data\arrayCollection;

use df;
use df\data;

trait TMutableHashMap
{
    use TReadable;

    /**
     * Get all keys in array, enforce string formatting
     */
    public function getKeys(): data\IReadable
    {
        return new static(array_map('strval', array_keys($this->items)));
    }


    /**
     * Retrieve a single entry
     */
    public function get(string $key)
    {
        if (isset($this->items[$key])) {
            return $this->items[$key];
        }

        return null;
    }

    /**
     * Retrieve entry and remove from collection
     */
    public function pull(string $key)
    {
        if (isset($this->items[$key])) {
            $output = $this->items[$key];
            unset($this->items[$key]);
            return $output;
        }

        return null;
    }

    /**
     * Direct set a value
     */
    public function set(string $key, $value): data\IHashMap
    {
        $this->items[$key] = $value;
        return $this;
    }

    /**
     * True if any provided keys have a set value (not null)
     */
    public function has(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (isset($this->items[$key])) {
                return true;
            }
        }

        return false;
    }

    /**
     * True if all provided keys have a set value (not null)
     */
    public function hasAll(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (!isset($this->items[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * True if any provided keys are in the collection
     */
    public function hasKey(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (array_keys_exists($key, $this->items)) {
                return true;
            }
        }

        return false;
    }

    /**
     * True if all provided keys are in the collection
     */
    public function hasKeys(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (!array_keys_exists($key, $this->items)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Remove all values associated with $keys
     */
    public function remove(string ...$keys): data\IHashMap
    {
        $this->items = array_diff_key($this->items, array_flip($keys));
        return $this;
    }

    /**
     * Remove all values not associated with $keys
     */
    public function keep(string ...$keys): data\IHashMap {
        $this->items = array_intersect_key($this->items, array_flip($keys));
        return $this;
    }


    /**
     * Lookup a key by value
     */
    public function findKey($value, bool $strict=false): ?string
    {
        if (false === ($key = array_search($value, $this->items, $strict))) {
            return null;
        }

        return (string)$key;
    }


    /**
     *
     */
    public function clear(): data\IHashMap
    {
        $this->items = [];
        return $this;
    }

    /**
     *
     */
    public function clearKeys(): data\IHashMap
    {
        $this->items = array_values($this->items);
        return $this;
    }


    /**
     * Collapse multi dimensional array to flat
     */
    public function collapse(bool $unique=false, bool $removeNull=false): data\IHashMap
    {
        $this->items = data\Arr::collapse($this->items, true, $unique, $removeNull);
        return $this;
    }

    /**
     * Collapse without the keys
     */
    public function collapseValues(bool $unique=false, bool $removeNull=false): data\IHashMap
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
     * Switch key case for all entries
     */
    public function changeKeyCase(int $case=CASE_LOWER): data\IHashMap
    {
        $this->items = array_change_key_case($this->items, $case);
        return $this;
    }


    /**
     * Map values of collection to $keys
     */
    public function combineWithKeys(iterable $keys): data\IHashMap
    {
        if (false !== ($result = array_combine(data\Arr::iterableToArray($keys), $this->items))) {
            $this->items = $result;
        }

        return $this;
    }

    /**
     * Map $values to values of collection as keys
     */
    public function combineWithValues(iterable $values): data\IHashMap
    {
        if (false !== ($result = array_combine($this->items, data\Arr::iterableToArray($values)))) {
            $this->items = $result;
        }

        return $this;
    }


    /**
     * Replace all values with $value
     */
    public function fill($value): data\IHashMap
    {
        $this->items = array_fill(array_keys($this->items), $value);
        return $this;
    }

    /**
     * Flip keys and values
     */
    public function flip(): data\IHashMap
    {
        $this->items = array_flip($this->items);
        return $this;
    }


    /**
     * Merge all passed collections into one
     */
    public function merge(iterable ...$arrays): data\IHashMap
    {
        $this->items = array_merge($this->items, ...data\Arr::iterablesToArrays(...$arrays));
        return $this;
    }

    /**
     * Merge EVERYTHING :D
     */
    public function mergeRecursive(iterable ...$arrays): data\IHashMap
    {
        $this->items = array_merge_recursive($this->items, ...data\Arr::iterablesToArrays(...$arrays));
        return $this;
    }


    /**
     * Like merge, but replaces.. obvs
     */
    public function replace(iterable ...$arrays): data\IHashMap
    {
        $this->items = array_replace($this->items, ...data\Arr::iterablesToArrays(...$arrays));
        return $this;
    }

    /**
     * Replace EVERYTHING :D
     */
    public function replaceRecursive(iterable ...$arrays): data\IHashMap
    {
        $this->items = array_replace_recursive($this->items, ...data\Arr::iterablesToArrays(...$arrays));
        return $this;
    }


    /**
     * Remove $offet + $length items
     */
    public function removeSlice(int $offset, int $length=null, data\IHashMap &$removed=null): data\IHashMap
    {
        if ($length === null) {
            $length = count($this->items);
        }

        $removed = new static(
            array_splice($this->items, $offset, $length)
        );

        return $this;
    }

    /**
     * Like removeSlice, but leaves a present behind
     */
    public function replaceSlice(int $offset, int $length=null, iterable $replacement, data\IHashMap &$removed=null): data\IHashMap
    {
        if ($length === null) {
            $length = count($this->items);
        }

        $removed = new static(
            array_splice($this->items, $offset, $length, data\Arr::iterableToArray($replacement))
        );

        return $this;
    }


    /**
     * Remove duplicates from collection
     */
    public function unique(int $flags=SORT_STRING): data\IHashMap
    {
        $this->items = array_unique($this->items, $flags);
    }


    /**
     * Iterate each entry
     */
    public function walk(callable $callback, $data=null): data\IHashMap
    {
        array_walk($this->items, $callback, $data);
        return $this;
    }

    /**
     * Iterate everything
     */
    public function walkRecursive(callable $callback, $data=null): data\IHashMap
    {
        array_walk_recursive($this->items, $callback, $data);
        return $this;
    }
}
