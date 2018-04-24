<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data\arrayCollection;

use df;
use df\data;

trait THashMap
{
    use TReadable;
    use TSortable;

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
        return $this->items[$key] ?? null;
    }

    /**
     * Retrieve entry and remove from collection
     */
    public function pull(string $key)
    {
        $output = $this->items[$key] ?? null;

        if (static::MUTABLE) {
            unset($this->items[$key]);
        }

        return $output;
    }

    /**
     * Direct set a value
     */
    public function set(string $key, $value): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        $output->items[$key] = $value;
        return $output;
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
        $output = static::MUTABLE ? $this : $this->copy();
        $output->items = array_diff_key($output->items, array_flip($keys));
        return $output;
    }

    /**
     * Remove all values not associated with $keys
     */
    public function keep(string ...$keys): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        $output->items = array_intersect_key($output->items, array_flip($keys));
        return $output;
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
     * Reset all values
     */
    public function clear(): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        $output->items = [];
        return $output;
    }

    /**
     * Remove all keys
     */
    public function clearKeys(): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        $output->items = array_values($output->items);
        return $output;
    }


    /**
     * Collapse multi dimensional array to flat
     */
    public function collapse(bool $unique=false, bool $removeNull=false): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        $output->items = data\Arr::collapse($output->items, true, $unique, $removeNull);
        return $output;
    }

    /**
     * Collapse without the keys
     */
    public function collapseValues(bool $unique=false, bool $removeNull=false): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        $output->items = data\Arr::collapse($output->items, false, $unique, $removeNull);
        return $output;
    }


    /**
     * Pull first item
     */
    public function pop()
    {
        if (static::MUTABLE) {
            return array_pop($this->items);
        } else {
            return $this->getLast();
        }
    }

    /**
     * Pull last item
     */
    public function shift()
    {
        if (static::MUTABLE) {
            return array_shift($this->items);
        } else {
            return $this->getFirst();
        }
    }


    /**
     * Switch key case for all entries
     */
    public function changeKeyCase(int $case=CASE_LOWER): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        $output->items = array_change_key_case($output->items, $case);
        return $output;
    }


    /**
     * Map values of collection to $keys
     */
    public function combineWithKeys(iterable $keys): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();

        if (false !== ($result = array_combine(data\Arr::iterableToArray($keys), $output->items))) {
            $output->items = $result;
        }

        return $output;
    }

    /**
     * Map $values to values of collection as keys
     */
    public function combineWithValues(iterable $values): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();

        if (false !== ($result = array_combine($output->items, data\Arr::iterableToArray($values)))) {
            $output->items = $result;
        }

        return $output;
    }


    /**
     * Replace all values with $value
     */
    public function fill($value): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        $output->items = array_fill_keys(array_keys($output->items), $value);
        return $output;
    }

    /**
     * Flip keys and values
     */
    public function flip(): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        $output->items = array_flip($output->items);
        return $output;
    }


    /**
     * Merge all passed collections into one
     */
    public function merge(iterable ...$arrays): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        $output->items = array_merge($output->items, ...data\Arr::iterablesToArrays(...$arrays));
        return $output;
    }

    /**
     * Merge EVERYTHING :D
     */
    public function mergeRecursive(iterable ...$arrays): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        $output->items = array_merge_recursive($output->items, ...data\Arr::iterablesToArrays(...$arrays));
        return $output;
    }


    /**
     * Like merge, but replaces.. obvs
     */
    public function replace(iterable ...$arrays): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        $output->items = array_replace($output->items, ...data\Arr::iterablesToArrays(...$arrays));
        return $output;
    }

    /**
     * Replace EVERYTHING :D
     */
    public function replaceRecursive(iterable ...$arrays): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        $output->items = array_replace_recursive($output->items, ...data\Arr::iterablesToArrays(...$arrays));
        return $output;
    }



    /**
     * Remove $offet + $length items
     */
    public function removeSlice(int $offset, int $length=null, data\IHashMap &$removed=null): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();

        if ($length === null) {
            $length = count($output->items);
        }

        $removed = new static(
            array_splice($output->items, $offset, $length)
        );

        return $output;
    }

    /**
     * Like removeSlice, but leaves a present behind
     */
    public function replaceSlice(int $offset, int $length=null, iterable $replacement, data\IHashMap &$removed=null): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();

        if ($length === null) {
            $length = count($output->items);
        }

        $removed = new static(
            array_splice($output->items, $offset, $length, data\Arr::iterableToArray($replacement))
        );

        return $output;
    }


    /**
     * Remove duplicates from collection
     */
    public function unique(int $flags=SORT_STRING): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        $output->items = array_unique($output->items, $flags);
        return $output;
    }


    /**
     * Iterate each entry
     */
    public function walk(callable $callback, $data=null): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        array_walk($output->items, $callback, $data);
        return $output;
    }

    /**
     * Iterate everything
     */
    public function walkRecursive(callable $callback, $data=null): data\IHashMap
    {
        $output = static::MUTABLE ? $this : $this->copy();
        array_walk_recursive($output->items, $callback, $data);
        return $output;
    }
}
