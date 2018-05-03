<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\data;

use df;

interface ISortable
{
    public function sort(int $flags=\SORT_REGULAR): ISortable; // asort
    public function reverseSort(int $flags=\SORT_REGULAR): ISortable; // arsort
    public function sortBy(callable $callable): ISortable; // uasort

    public function sortNatural(): ISortable; // natsort
    public function sortCaseNatural(): ISortable; // natcasesort

    public function sortValues(int $flags=\SORT_REGULAR): ISortable; // sort
    public function reverseSortValues(int $flags=\SORT_REGULAR): ISortable; // rsort
    public function sortValuesBy(callable $callback): ISortable; // usort

    public function sortKeys(int $flags=\SORT_REGULAR): ISortable; // ksort
    public function reverseSortKeys(int $flags=\SORT_REGULAR): ISortable; // krsort
    public function sortKeysBy(callable $callback): ISortable; // uksort

    public function reverse(): ISortable; // array_reverse
    public function reverseValues(): ISortable; // array_reverse
    public function shuffle(): ISortable; // kshuffle
    public function shuffleValues(): ISortable; // shuffle
}
