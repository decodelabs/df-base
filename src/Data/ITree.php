<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Data;

use Df;
use Df\Flex\Input\Sanitizer;

interface ITree extends IHashMap, IValueProvider
{
    public function __set(string $key, $value): IHashMap;
    public function __get(string $key): IHashMap;
    public function __isset(string $key): bool;
    public function __unset(string $key): IHashMap;

    public function setNode(string $key, $value): ITree;
    public function getNode(string $key): ITree;
    public function hasNode(string ...$keys): bool;
    public function hasAllNodes(string ...$keys): bool;

    public function sanitize(string $key): Sanitizer;
    public function sanitizeValue(): Sanitizer;

    public function setValue($value): IHashMap;
    public function hasValue(): bool;
    public function hasAnyValue(): bool;
    public function isValue($value, bool $strict): bool;


    public static function fromDelimitedString(string $string, string $setDelimiter='&', string $valueDelimiter='='): ITree;
    public function toDelimitedString(string $setDelimiter='&', string $valueDelimiter='='): string;
    public function toDelimitedSet(bool $urlEncode=false, string $prefix=null): array;

    public function getChildren(): array;
}
