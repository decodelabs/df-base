<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data\arrayCollection;

use df;
use df\data;

trait TImmutableSortable
{
    /**
     * Sort values, keep keys
     */
    public function sort(int $flags=\SORT_REGULAR): data\ISortable
    {
        $output = $this->copyImmutable();
        asort($output->items, $flags);
        return $output;
    }

    /**
     * Reverse sort values, keep keys
     */
    public function reverseSort(int $flags=\SORT_REGULAR): data\ISortable
    {
        $output = $this->copyImmutable();
        arsort($output->items, $flags);
        return $output;
    }

    /**
     * Sort values using callback, keep keys
     */
    public function sortBy(callable $callable): data\ISortable
    {
        $output = $this->copyImmutable();
        uasort($output->items, $callable);
        return $output;
    }


    /**
     * Natural sort values, keep keys
     */
    public function sortNatural(): data\ISortable
    {
        $output = $this->copyImmutable();
        natsort($output->items);
        return $output;
    }

    /**
     * Natural sort values, case insensitive, keep keys
     */
    public function sortCaseNatural(): data\ISortable
    {
        $output = $this->copyImmutable();
        natcasesort($output->items);
        return $output;
    }


    /**
     * Sort values, ignore keys
     */
    public function sortValues(int $flags=\SORT_REGULAR): data\ISortable
    {
        $output = $this->copyImmutable();
        sort($output->items, $flags);
        return $output;
    }

    /**
     * Reverse sort values, ignore keys
     */
    public function reverseSortValues(int $flags=\SORT_REGULAR): data\ISortable
    {
        $output = $this->copyImmutable();
        rsort($output->items, $flags);
        return $output;
    }

    /**
     * Sort values by callback, ignore keys
     */
    public function sortValuesBy(callable $callback): data\ISortable
    {
        $output = $this->copyImmutable();
        usort($output->items, $callback);
        return $output;
    }


    /**
     * Sort values by key
     */
    public function sortKeys(int $flags=\SORT_REGULAR): data\ISortable
    {
        $output = $this->copyImmutable();
        ksort($output->items, $flags);
        return $output;
    }

    /**
     * Reverse sort values by key
     */
    public function reverseSortKeys(int $flags=\SORT_REGULAR): data\ISortable
    {
        $output = $this->copyImmutable();
        krsort($output->items, $flags);
        return $output;
    }

    /**
     * Sort values by key using callback
     */
    public function sortKeysBy(callable $callback): data\ISortable
    {
        $output = $this->copyImmutable();
        uksort($output->items, $callback);
        return $output;
    }


    /**
     * Reverse all entries
     */
    public function reverse(): data\ISortable
    {
        $output = $this->copyImmutable();
        $output->items = array_reverse($output->items, true);
        return $output;
    }

    /**
     * Reverse all entries, ignore keys
     */
    public function reverseValues(): data\ISortable
    {
        $output = $this->copyImmutable();
        $output->items = array_reverse($output->items, false);
        return $output;
    }

    /**
     * Randomise order, keep keys
     */
    public function shuffle(): data\ISortable
    {
        $output = $this->copyImmutable();
        $output->items = data\Arr::kshuffle($output->items);
        return $output;
    }

    /**
     * Randomise order, ignore keys
     */
    public function shuffleValues(): data\ISortable
    {
        $output = $this->copyImmutable();
        shuffle($output->items);
        return $output;
    }
}
