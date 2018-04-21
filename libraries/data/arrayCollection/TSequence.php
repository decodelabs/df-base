<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data\arrayCollection;

use df;
use df\data;

trait TSequence
{
    /**
     * Direct set items
     */
    public function __construct(iterable $items)
    {
        $this->items = array_values(
            data\Arr::iterableToArray($items)
        );
    }


    /**
     * Get all keys in array, enforce int formatting
     */
    public function getKeys(): data\IReadable
    {
        return new static(array_map('intval', array_keys($this->items)));
    }


    /**
     * Get item by index
     */
    public function get(int $key)
    {
        if ($key < 0) {
            $key += count($this->items);

            if ($key < 0) {
                throw df\Exception::EOutOfBounds('Index '.$key.' is not accessible', null, $this);
            }
        }

        return $this->items[$key] ?? null;
    }


    /**
     * True if any provided keys have a set value (not null)
     */
    public function has(int ...$keys): bool
    {
        $count = count($this->items);

        foreach ($keys as $key) {
            if ($key < 0) {
                $key += $count;

                if ($key < 0) {
                    throw df\Exception::EOutOfBounds('Index '.$key.' is not accessible', null, $this);
                }
            }

            if (isset($this->items[$key])) {
                return true;
            }
        }

        return false;
    }

    /**
     * True if all provided keys have a set value (not null)
     */
    public function hasAll(int ...$keys): bool
    {
        $count = count($this->items);

        foreach ($keys as $key) {
            if ($key < 0) {
                $key += $count;

                if ($key < 0) {
                    throw df\Exception::EOutOfBounds('Index '.$key.' is not accessible', null, $this);
                }
            }

            if (!isset($this->items[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * True if any provided keys are in the collection
     */
    public function hasKey(int ...$keys): bool
    {
        $count = count($this->items);

        foreach ($keys as $key) {
            if ($key < 0) {
                $key += $count;

                if ($key < 0) {
                    throw df\Exception::EOutOfBounds('Index '.$key.' is not accessible', null, $this);
                }
            }

            if (array_keys_exists($key, $this->items)) {
                return true;
            }
        }

        return false;
    }

    /**
     * True if all provided keys are in the collection
     */
    public function hasKeys(int ...$keys): bool
    {
        $count = count($this->items);

        foreach ($keys as $key) {
            if ($key < 0) {
                $key += $count;

                if ($key < 0) {
                    throw df\Exception::EOutOfBounds('Index '.$key.' is not accessible', null, $this);
                }
            }

            if (!array_keys_exists($key, $this->items)) {
                return false;
            }
        }

        return true;
    }



    /**
     * Lookup a key by value
     */
    public function findKey($value, bool $strict=false): ?int
    {
        if (false === ($key = array_search($value, $this->items, $strict))) {
            return null;
        }

        return (int)$key;
    }


    /**
     * Create a new sequence with numeric range
     */
    public static function createFill(int $length, $value): data\ISequence
    {
        return new static(array_fill(0, $length, $value));
    }

    /**
     * Create a collection of numbers
     */
    public function createRange(int $start, int $end, int $step=1): data\ISequence
    {
        return new static(range($start, $end, $step));
    }


    /**
     * Prepare an index
     */
    protected function normalizeKey(int $key): int
    {
        if ($key < 0) {
            $key += count($this->items);

            if ($key < 0) {
                throw df\Exception::EOutOfBounds('Index '.$key.' is not accessible', null, $this);
            }
        }

        return $key;
    }
}
