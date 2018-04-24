<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\data;

use df;
use df\data;
use df\lang;

/**
 * All methods returning IReadable MUST be immutable,
 * regardless of whether implementation is mutable
 */
interface IReadable extends ICollection, \Countable, \ArrayAccess, lang\IPipe
{
    public function getFirst(callable $filter=null);
    public function getLast(callable $filter=null);
    public function getRandom();

    public function getKeys(): IReadable;

    public function contains($value, bool $strict=false): bool;
    public function containsRecursive($value, bool $strict=false): bool;

    public function slice(int $offset, int $length=null): IReadable;
    public function sliceRandom(int $number): IReadable;

    public function chunk(int $size): IReadable;
    public function chunkValues(int $size): IReadable;

    public function countValues(): IReadable;

    public function diffAssoc(iterable ...$arrays): IReadable; // array_diff_assoc
    public function diffAssocBy(callable $keyCallback, iterable ...$arrays): IReadable; // array_diff_uassoc
    public function diffAssocByValue(callable $valueCallback, iterable ...$arrays): IReadable; // array_udiff_assoc
    public function diffAssocAll(callable $valueCallback, callable $keyCallback, iterable ...$arrays): IReadable; // array_udiff_uassoc
    public function diffValues(iterable ...$arrays): IReadable; // array_diff
    public function diffValuesBy(callable $valueCallback, iterable ...$arrays): IReadable; // array_udiff
    public function diffKeys(iterable ...$arrays): IReadable; // array_diff_key
    public function diffKeysBy(callable $keyCallback, iterable ...$arrays): IReadable; // array_diff_ukey

    public function intersectAssoc(iterable ...$arrays): IReadable; // array_intersect_assoc
    public function intersectAssocBy(callable $keyCallback, iterable ...$arrays): IReadable; // array_intersect_uassoc
    public function intersectAssocByValue(callable $valueCallback, iterable ...$arrays): IReadable; // array_uintersect_assoc
    public function intersectAssocAll(callable $valueCallback, callable $keyCallback, iterable ...$arrays): IReadable; // array_uintersect_uassoc
    public function intersectValues(iterable ...$arrays): IReadable; // array_intersect
    public function intersectValuesBy(callable $valueCallback, iterable ...$arrays): IReadable; // array_uintersect
    public function intersectKeys(iterable ...$arrays): IReadable; // array_intersect_key
    public function intersectKeysBy(callable $keyCallback, iterable ...$arrays): IReadable; // array_intersect_ukey

    public function filter(callable $callback=null): IReadable;
    public function map(callable $callback, iterable ...$arrays): IReadable;
    public function mapSelf(callable $callback): IReadable;
    public function reduce(callable $callback, $initial=null);

    public function getSum(callable $filter=null);
    public function getProduct(callable $filter=null);
    public function getAvg(callable $filter=null);

    public function pluck(string $valueKey, string $indexKey=null): IReadable;
}
