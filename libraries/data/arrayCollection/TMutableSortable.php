<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data\arrayCollection;

use df;
use df\data;

trait TMutableSortable
{
    /**
     * Sort values, keep keys
     */
    public function sort(int $flags=\SORT_REGULAR): data\ISortable
    {
        asort($this->items, $flags);
        return $this;
    }

    /**
     * Reverse sort values, keep keys
     */
    public function reverseSort(int $flags=\SORT_REGULAR): data\ISortable
    {
        arsort($this->items, $flags);
        return $this;
    }

    /**
     * Sort values using callback, keep keys
     */
    public function sortBy(callable $callable): data\ISortable
    {
        uasort($this->items, $callable);
        return $this;
    }


    /**
     * Natural sort values, keep keys
     */
    public function sortNatural(): data\ISortable
    {
        natsort($this->items);
        return $this;
    }

    /**
     * Natural sort values, case insensitive, keep keys
     */
    public function sortCaseNatural(): data\ISortable
    {
        natcasesort($this->items);
        return $this;
    }


    /**
     * Sort values, ignore keys
     */
    public function sortValues(int $flags=\SORT_REGULAR): data\ISortable
    {
        sort($this->items, $flags);
        return $this;
    }

    /**
     * Reverse sort values, ignore keys
     */
    public function reverseSortValues(int $flags=\SORT_REGULAR): data\ISortable
    {
        rsort($this->items, $flags);
        return $this;
    }

    /**
     * Sort values by callback, ignore keys
     */
    public function sortValuesBy(callable $callback): data\ISortable
    {
        usort($this->items, $callback);
        return $this;
    }


    /**
     * Sort values by key
     */
    public function sortKeys(int $flags=\SORT_REGULAR): data\ISortable
    {
        ksort($this->items, $flags);
        return $this;
    }

    /**
     * Reverse sort values by key
     */
    public function reverseSortKeys(int $flags=\SORT_REGULAR): data\ISortable
    {
        krsort($this->items, $flags);
        return $this;
    }

    /**
     * Sort values by key using callback
     */
    public function sortKeysBy(callable $callback): data\ISortable
    {
        uksort($this->items, $callback);
        return $this;
    }


    /**
     * Reverse all entries
     */
    public function reverse(): data\ISortable
    {
        $this->items = array_reverse($this->items, true);
        return $this;
    }

    /**
     * Reverse all entries, ignore keys
     */
    public function reverseValues(): data\ISortable
    {
        $this->items = array_reverse($this->items, false);
        return $this;
    }

    /**
     * Randomise order, keep keys
     */
    public function shuffle(): data\ISortable
    {
        $this->items = data\Arr::kshuffle($this->items);
        return $this;
    }

    /**
     * Randomise order, ignore keys
     */
    public function shuffleValues(): data\ISortable
    {
        shuffle($this->items);
        return $this;
    }
}
