<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Cache;

use Df\Core\Cache\Driver;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

interface Store extends CacheItemPoolInterface, CacheInterface, \ArrayAccess
{
    public function getNamespace(): string;
    public function getDriver(): Driver;

    public function fetch(string $key, callable $generator);

    public function __set(string $key, $value);
    public function __get(string $key);
    public function __isset(string $key): bool;
    public function __unset(string $key);

    public function clearDeferred(): bool;


    public function pileUpIgnore(): Store;
    public function pileUpPreempt(int $preemptTime=null): Store;
    public function pileUpSleep(int $time=null, int $attempts=null): Store;
    public function pileUpValue(): Store;

    public function setPileUpPolicy(string $policy): Store;
    public function getPileUpPolicy(): string;

    public function setPreemptTime(int $preemptTime): Store;
    public function getPreemptTime(): int;

    public function setSleepTime(int $time): Store;
    public function getSleepTime(): int;
    public function setSleepAttempts(int $attempts): Store;
    public function getSleepAttempts(): int;
}
