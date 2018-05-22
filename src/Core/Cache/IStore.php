<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Cache;

use Df;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

interface IStore extends CacheItemPoolInterface, CacheInterface, \ArrayAccess
{
    public function getNamespace(): string;
    public function getDriver(): IDriver;

    public function __set(string $key, $value);
    public function __get(string $key);
    public function __isset(string $key): bool;
    public function __unset(string $key);

    public function clearDeferred(): bool;
}
