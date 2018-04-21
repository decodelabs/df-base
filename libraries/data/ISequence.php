<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\data\subLib;

use df;
use df\data;


interface ISequence extends ICollection
{
    public function get(int $key);
    public function pull(int $key);
    public function set(int $key, $value): ISequence;
    public function has(int ...$keys): bool;
    public function hasAny(int ...$keys): bool;
    public function hasKey(int ...$keys): bool;
    public function hasAnyKey(int ...$keys): bool;
    public function remove(int ...$keys): ISequence;

    public function clear(): ISequence;
    public function clearKeys(): ISequence;

    public function collapse(): ISequence;

    public function pop();
    public function shift();
    public function append(...$values): ISequence;
    public function prepend(...$values): ISequence;

    public function combineWithKeys(iterable $keys): ISequence;
    public function combineWithValues(iterable $values): ISequence;

    public function fill($value): ISequence;
    public static function createFillRange(int $start, int $length, $value): ISequence;

    public function merge(iterable ...$arrays): ISequence;
    public function mergeRecursive(iterable ...$arrays): ISequence;

    public function replace(iterable ...$arrays): ISequence;
    public function replaceRecursive(iterable ...$arrays): ISequence;

    public function pad(int $size, $value): ISequence;

    public function removeSlice(int $offset, int $length=null, ISequence &$removed=null): ISequence;
    public function replaceSlice(int $offset, int $length=null, iterable $replacement, ISequence &$removed=null): ISequence;

    public function unique(int $flags=SORT_STRING): ISequence;

    public function walk(callable $callback, $data=null): ISequence;
    public function walkRecursive(callable $callback, $data=null): ISequence;

    public function createRange(int $start, int $end, int $step=1): ISequence;
    public function createByMap(int $number, callable $callback): ISequence;
}
