<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Data;

use Df;

interface ISequence extends IReadable, ISortable
{
    public function get(int $key);
    public function pull(int $key);
    public function set(int $key, $value): ISequence;
    public function put(int $key, $value): ISequence;
    public function has(int ...$keys): bool;
    public function hasAll(int ...$keys): bool;
    public function hasKey(int ...$keys): bool;
    public function hasKeys(int ...$keys): bool;
    public function remove(int ...$keys): ISequence;
    public function keep(int ...$keys): ISequence;

    public function findKey($value, bool $strict=false): ?int;

    public function clear(): ISequence;
    public function clearKeys(): ISequence;

    public function collapse(bool $unique=false, bool $removeNull=false): ISequence;

    public function pop();
    public function shift();
    public function append(...$values): ISequence;
    public function prepend(...$values): ISequence;

    public function fill($value): ISequence;
    public static function createFill(int $length, $value): ISequence;

    public function merge(iterable ...$arrays): ISequence;
    public function mergeRecursive(iterable ...$arrays): ISequence;

    public function replace(iterable ...$arrays): ISequence;
    public function replaceRecursive(iterable ...$arrays): ISequence;

    public function padLeft(int $size, $value=null): ISequence;
    public function padRight(int $size, $value=null): ISequence;
    public function padBoth(int $size, $value=null): ISequence;

    public function removeSlice(int $offset, int $length=null, ISequence &$removed=null): ISequence;
    public function replaceSlice(int $offset, int $length=null, iterable $replacement, ISequence &$removed=null): ISequence;

    public function unique(int $flags=SORT_STRING): ISequence;

    public function walk(callable $callback, $data=null): ISequence;
    public function walkRecursive(callable $callback, $data=null): ISequence;

    public function createRange(int $start, int $end, int $step=1): ISequence;
}
