<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data\arrayCollection;

use df;
use df\data;


trait TTHashMap
{
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
     * Lookup a key by value
     */
    public function findKey($value, bool $strict=false): ?string
    {
        if (false === ($key = array_search($value, $this->items, $strict))) {
            return null;
        }

        return (string)$key;
    }
}
