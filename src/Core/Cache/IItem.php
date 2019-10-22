<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Cache;

use Psr\Cache\CacheItemInterface;

interface IItem extends CacheItemInterface
{
    public function getStore(): IStore;
    public function isMiss(): bool;

    public function setExpiration($expiration): IItem;
    public function getExpiration(): ?\DateTimeInterface;
    public function getExpirationTimestamp(): ?int;
    public function getTimeRemaining(): ?\DateInterval;

    public function pileUpIgnore(): IItem;
    public function pileUpPreempt(int $preemptTime=null): IItem;
    public function pileUpSleep(int $time=null, int $attempts=null): IItem;
    public function pileUpValue($value): IItem;

    public function setPileUpPolicy(string $policy): IItem;
    public function getPileUpPolicy(): string;

    public function setPreemptTime(int $preemptTime): IItem;
    public function getPreemptTime(): int;

    public function setSleepTime(int $time): IItem;
    public function getSleepTime(): int;
    public function setSleepAttempts(int $attempts): IItem;
    public function getSleepAttempts(): int;

    public function setFallbackValue($value): IItem;
    public function getFallbackValue();

    public function lock($ttl=null): bool;
    public function save(): bool;
    public function defer(): bool;
    public function update($value, $ttl=null): bool;
    public function extend($ttl=null): bool;
    public function delete(): bool;
}
