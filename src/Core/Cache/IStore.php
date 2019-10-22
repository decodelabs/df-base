<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

interface IStore extends CacheItemPoolInterface, CacheInterface, \ArrayAccess
{
    public function getNamespace(): string;
    public function getDriver(): IDriver;

    public function fetch(string $key, callable $generator);

    public function __set(string $key, $value);
    public function __get(string $key);
    public function __isset(string $key): bool;
    public function __unset(string $key);

    public function clearDeferred(): bool;


    public function pileUpIgnore(): IStore;
    public function pileUpPreempt(int $preemptTime=null): IStore;
    public function pileUpSleep(int $time=null, int $attempts=null): IStore;
    public function pileUpValue(): IStore;

    public function setPileUpPolicy(string $policy): IStore;
    public function getPileUpPolicy(): string;

    public function setPreemptTime(int $preemptTime): IStore;
    public function getPreemptTime(): int;

    public function setSleepTime(int $time): IStore;
    public function getSleepTime(): int;
    public function setSleepAttempts(int $attempts): IStore;
    public function getSleepAttempts(): int;
}
