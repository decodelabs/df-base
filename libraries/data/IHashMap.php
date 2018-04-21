<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\data\subLib;

use df;
use df\data;


interface IHashMap extends ICollection
{
    public function get(string $key);
    public function pull(string $key);
    public function set(string $key, $value): IHashMap;
    public function has(string ...$keys): bool;
    public function hasAny(string ...$keys): bool;
    public function hasKey(string ...$keys): bool;
    public function hasAnyKey(string ...$keys): bool;
    public function remove(string ...$keys): IHashMap;

    public function clear(): IHashMap;
    public function clearKeys(): IHashMap;

    public function collapse(): IHashMap;

    public function pop();
    public function shift();

    public function changeKeyCase(int $case=CASE_LOWER): IHashMap;

    public function combineWithKeys(iterable $keys): IHashMap;
    public function combineWithValues(iterable $values): IHashMap;

    public function fill($value): IHashMap;
    public function flip(): IHashMap;

    public function merge(iterable ...$arrays): IHashMap;
    public function mergeRecursive(iterable ...$arrays): IHashMap;

    public function replace(iterable ...$arrays): IHashMap;
    public function replaceRecursive(iterable ...$arrays): IHashMap;

    public function removeSlice(int $offset, int $length=null, IHashMap &$removed=null): IHashMap;
    public function replaceSlice(int $offset, int $length=null, iterable $replacement, IHashMap &$removed=null): IHashMap;

    public function unique(int $flags=SORT_STRING): IHashMap;

    public function walk(callable $callback, $data=null): IHashMap;
    public function walkRecursive(callable $callback, $data=null): IHashMap;

    public function createByMap(array $keys, callable $callback): IHashMap;
}
