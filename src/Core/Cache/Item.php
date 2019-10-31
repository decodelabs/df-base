<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Cache;

use Df\Time\Date;
use Df\Time\Interval;

use Psr\Cache\CacheItemInterface;

class Item implements IItem
{
    const IGNORE = 'ignore';
    const PREEMPT = 'preempt';
    const SLEEP = 'sleep';
    const VALUE = 'value';

    const LOCK_TTL = 30;

    protected $key;
    protected $value;
    protected $isHit = false;
    protected $fetched = false;

    protected $store;
    protected $expiration;
    protected $locked = false;

    protected $pileUpPolicy = null;
    protected $preemptTime = null;
    protected $sleepTime = null;
    protected $sleepAttempts = null;
    protected $fallbackValue = null;

    /**
     * Init with store and key
     */
    public function __construct(IStore $store, string $key)
    {
        $this->key = $key;
        $this->store = $store;
    }

    /**
     * Get parent store
     */
    public function getStore(): IStore
    {
        return $this->store;
    }

    /**
     * Returns the key for the current cache item.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Sets the value represented by this cache item.
     */
    public function set($value): CacheItemInterface
    {
        $this->value = $value;
        $this->isHit = true;
        $this->fetched = true;
        return $this;
    }

    /**
     * Retrieves the value of the item from the cache associated with this object's key.
     */
    public function get()
    {
        if (!$this->isHit()) {
            return null;
        }

        return $this->value;
    }

    /**
     * Confirms if the cache item lookup resulted in a cache hit.
     */
    public function isHit(): bool
    {
        $this->ensureFetched();

        if (!$this->isHit) {
            return false;
        }

        if ($this->expiration) {
            return $this->expiration->timestamp > time();
        }

        return true;
    }

    /**
     * Invert of isHit()
     */
    public function isMiss(): bool
    {
        return !$this->isHit();
    }

    /**
     * Sets the expiration time for this cache item.
     */
    public function expiresAt($expiration): CacheItemInterface
    {
        $this->expiration = Date::instance($expiration);
        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     */
    public function expiresAfter($time): CacheItemInterface
    {
        if ($time === null) {
            $this->expiration = null;
            return $this;
        }

        $interval = Interval::instance($time);
        $date = new Date();
        $date->add($interval);

        $this->expiration = $date;
        return $this;
    }


    /**
     * Work out best expiration from value
     */
    public function setExpiration($expiration): IItem
    {
        if ($expiration instanceof \DateInterval || is_string($expiration)) {
            return $this->expiresAfter($expiration);
        } else {
            return $this->expiresAt($expiration);
        }
    }


    /**
     * Get actual expiration date (if not permanent)
     */
    public function getExpiration(): ?\DateTimeInterface
    {
        return $this->expiration;
    }

    /**
     * Get expiration as timestamp int
     */
    public function getExpirationTimestamp(): ?int
    {
        if (!$this->expiration) {
            return null;
        }

        return $this->expiration->timestamp;
    }


    /**
     * Get time until expiration
     */
    public function getTimeRemaining(): ?\DateInterval
    {
        if (!$this->expiration) {
            return null;
        }

        $output = Date::now()->diff($this->expiration);

        if ($output->invert) {
            return new Interval(0);
        } else {
            return Interval::instance($output);
        }
    }


    /**
     * Set pile up policy to ignore
     */
    public function pileUpIgnore(): IItem
    {
        $this->pileUpPolicy = self::IGNORE;
        return $this;
    }

    /**
     * Set pile up policy to preempt
     */
    public function pileUpPreempt(int $preemptTime=null): IItem
    {
        $this->pileUpPolicy = self::PREEMPT;

        if ($preemptTime !== null) {
            $this->preemptTime = $preemptTime;
        }

        return $this;
    }

    /**
     * Set pile up policy to sleep
     */
    public function pileUpSleep(int $time=null, int $attempts=null): IItem
    {
        $this->pileUpPolicy = self::SLEEP;

        if ($time !== null) {
            $this->sleepTime = $time;
        }

        if ($attempts !== null) {
            $this->sleepAttempts = $attempts;
        }

        return $this;
    }

    /**
     * Set pile up policy to return value
     */
    public function pileUpValue($value): IItem
    {
        $this->pileUpPolicy = self::VALUE;
        $this->fallbackValue = $value;
        return $this;
    }


    /**
     * Set pile up policy
     */
    public function setPileUpPolicy(string $policy): IItem
    {
        $this->pileUpPolicy = $policy;
        return $this;
    }

    /**
     * Get pile up policy
     */
    public function getPileUpPolicy(): string
    {
        return $this->pileUpPolicy ?? $this->store->getPileUpPolicy();
    }


    /**
     * Replace preempt time
     */
    public function setPreemptTime(int $preemptTime): IItem
    {
        $this->preemptTime = $preemptTime;
        return $this;
    }

    /**
     * Get preempt time
     */
    public function getPreemptTime(): int
    {
        return $this->preemptTime ?? $this->store->getPreemptTime();
    }


    /**
     * Replace sleep time
     */
    public function setSleepTime(int $time): IItem
    {
        $this->sleepTime = $time;
        return $this;
    }

    /**
     * Get sleep time
     */
    public function getSleepTime(): int
    {
        return $this->sleepTime ?? $this->store->getSleepTime();
    }

    /**
     * Replace sleep attempts
     */
    public function setSleepAttempts(int $attempts): IItem
    {
        $this->sleepAttempts = $attempts;
        return $this;
    }

    /**
     * Get sleep attempts
     */
    public function getSleepAttempts(): int
    {
        return $this->sleepAttempts ?? $this->store->getSleepAttempts();
    }


    /**
     * Replace fallback value
     */
    public function setFallbackValue($value): IItem
    {
        $this->fallbackValue = $value;
        return $this;
    }

    /**
     * Get fallback value
     */
    public function getFallbackValue()
    {
        return $this->fallbackValue;
    }


    /**
     * Add lock entry to avoid multiple processes regenerating value
     */
    public function lock($ttl=null): bool
    {
        $this->locked = true;

        if ($ttl !== null) {
            $date = new Date();
            $ttl = Interval::instance($ttl);
            $date->add($ttl);
            $expires = $date->timestamp;
        } else {
            $expires = time() + static::LOCK_TTL;
        }

        return $this->store->getDriver()->storeLock(
            $this->store->getNamespace(),
            $this->key,
            $expires
        );
    }

    /**
     * Store item to driver
     */
    public function save(): bool
    {
        $this->ensureFetched();

        if ($this->locked) {
            $this->store->getDriver()->deleteLock(
                $this->store->getNamespace(),
                $this->key
            );

            $this->locked = false;
        }

        $created = time();
        $expires = null;

        if ($this->expiration) {
            $expires = $this->expiration->timestamp;
            $expires -= rand(0, (int)floor(($expires - $created) * 0.15));
        }

        $ttl = null;

        if ($expires) {
            $ttl = $expires - $created;

            if ($ttl < 0) {
                $this->delete();
                return false;
            }
        }

        return $this->store->getDriver()->store(
            $this->store->getNamespace(),
            $this->key,
            $this->value,
            $created,
            $expires
        );
    }

    /**
     * Defer saving until commit on pool
     */
    public function defer(): bool
    {
        return $this->store->saveDeferred($this);
    }

    /**
     * Set value and save
     */
    public function update($value, $ttl=null): bool
    {
        if ($ttl) {
            $this->expiresAfter($ttl);
        }

        $this->set($value);
        return $this->save();
    }

    /**
     * Re-store item
     */
    public function extend($ttl=null): bool
    {
        if ($ttl) {
            $this->expiresAfter($ttl);
        }

        $this->set($this->get());
        return $this->save();
    }

    /**
     * Delete current item
     */
    public function delete(): bool
    {
        $output = $this->store->getDriver()->delete(
            $this->store->getNamespace(),
            $this->key
        );

        if ($output) {
            $this->value = null;
            $this->isHit = false;
        }

        return $output;
    }


    /**
     * Ensure data has been fetched from driver
     */
    protected function ensureFetched(): void
    {
        if ($this->fetched) {
            return;
        }

        $time = time();
        $driver = $this->store->getDriver();

        $res = $driver->fetch(
            $this->store->getNamespace(),
            $this->key
        );

        if (!$res) {
            $this->isHit = false;
            $this->value = null;
        } else {
            $this->isHit = true;
            $this->value = $res[0];
            $this->expiration = Date::instance($res[1]);

            if ($this->expiration && $this->expiration->timestamp < $time) {
                $this->isHit = false;
                $this->value = null;

                $driver->delete(
                    $this->store->getNamespace(),
                    $this->key
                );
            }
        }

        $this->fetched = true;
        $policy = $this->getPileUpPolicy();

        if ($policy === self::IGNORE) {
            return;
        }

        $ttl = $this->expiration ? $this->expiration->timestamp - $time : null;

        if ($this->isHit) {
            if ($policy === self::PREEMPT
            && $ttl > 0
            && $ttl < $this->getPreemptTime()) {
                $lockExp = $driver->fetchLock(
                    $this->store->getNamespace(),
                    $this->key
                );

                if ($lockExp < $time) {
                    $lockExp = null;
                }

                if (!$lockExp) {
                    $this->isHit = false;
                    $this->value = null;
                }
            }

            return;
        }

        $lockExp = $driver->fetchLock(
            $this->store->getNamespace(),
            $this->key
        );

        if (!$lockExp) {
            return;
        }

        $options = array_unique([$policy, self::VALUE, self::SLEEP]);

        foreach ($options as $option) {
            switch ($option) {
                case self::VALUE:
                    if ($this->fallbackValue !== null) {
                        $this->value = $this->fallbackValue;
                        $this->isHit = true;
                        return;
                    }

                    break;

                case self::SLEEP:
                    if (($attempts = $this->getSleepAttempts()) < 1) {
                        return;
                    }

                    $time = $this->getSleepTime();

                    while ($attempts > 0) {
                        usleep($time * 1000);
                        $attempts--;

                        $res = $driver->fetch(
                            $this->store->getNamespace(),
                            $this->key
                        );

                        if ($res) {
                            $this->isHit = true;
                            $this->value = $res[0];
                            $this->expiration = Date::instance($res[1]);
                            return;
                        }
                    }

                    $this->isHit = false;
                    $this->value = null;

                    break;
            }
        }
    }
}
