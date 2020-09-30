<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Cache\Store;

use Df\Core\Cache\Store;
use Df\Core\Cache\Driver;
use Df\Core\Cache\Driver\PhpArray;
use Df\Core\Cache\Item;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException as CacheInvalidArgumentException;

use DecodeLabs\Exceptional;

class Generic implements Store
{
    protected $driver;
    protected $namespace;
    protected $deferred = [];

    protected $pileUpPolicy = Item::PREEMPT;
    protected $preemptTime = 30;
    protected $sleepTime = 500;
    protected $sleepAttempts = 10;

    /**
     * Init with namespace and driver
     */
    public function __construct(string $namespace, Driver $driver=null)
    {
        if (!$driver) {
            $driver = new PhpArray();
        }

        $this->driver = $driver;

        if ($namespace === '') {
            throw Exceptional::InvalidArgument(
                'Invalid empty cache namespace'
            );
        }

        $this->namespace = $namespace;
    }

    /**
     * Get active driver
     */
    public function getDriver(): Driver
    {
        return $this->driver;
    }

    /**
     * Get active namespace
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }


    /**
     * Fetches a value from the cache.
     */
    public function get($key, $default=null)
    {
        $item = $this->wrapSimpleErrors(function () use ($key) {
            return $this->getItem($key);
        });

        if ($item->isHit()) {
            return $item->get();
        } else {
            return $default;
        }
    }

    /**
     * Retrive item object, regardless of hit or miss
     */
    public function getItem($key): CacheItemInterface
    {
        $key = $this->validateKey($key);

        if (isset($this->deferred[$key])) {
            return clone $this->deferred[$key];
        }

        return new Item($this, $key);
    }

    /**
     * Obtains multiple cache items by their unique keys.
     */
    public function getMultiple($keys, $default=null): iterable
    {
        $items = $this->wrapSimpleErrors(function () use ($keys) {
            return $this->getItems($this->normalizeKeyList($keys));
        });

        return (function (iterable $items, $default=null) {
            foreach ($items as $key => $item) {
                if ($item->isHit()) {
                    yield $key => $item->get();
                } else {
                    yield $key => $default;
                }
            }
        })($items, $default);
    }

    /**
     * Retrieve a list of items
     */
    public function getItems(array $keys=[]): iterable
    {
        $output = [];

        foreach ($keys as $key) {
            $item = $this->getItem($key);
            $output[$item->getKey()] = $item;
        }

        return $output;
    }

    /**
     * Determines whether an item is present in the cache.
     */
    public function has($key, string ...$keys): bool
    {
        $keys = func_get_args();

        return $this->wrapSimpleErrors(function () use ($keys) {
            foreach ($keys as $key) {
                if ($this->hasItem($key)) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Confirms if the cache contains specified cache item.
     */
    public function hasItem($key): bool
    {
        return $this->getItem($key)->isHit();
    }

    /**
     * Deletes all items in the pool.
     */
    public function clear(): bool
    {
        $this->deferred = [];
        return $this->driver->clearAll($this->namespace);
    }

    /**
     * Forget things that have been deferred
     */
    public function clearDeferred(): bool
    {
        $this->deferred = [];
        return true;
    }

    /**
     * Delete an item from the cache by its unique key.
     */
    public function delete($key, string ...$keys): bool
    {
        $keys = func_get_args();
        return $this->wrapSimpleErrors(function () use ($keys) {
            return $this->deleteItems($keys);
        });
    }

    /**
     * Removes the item from the pool.
     */
    public function deleteItem($key, string ...$keys): bool
    {
        return $this->deleteItems(func_get_args());
    }

    /**
     * Deletes multiple cache items in a single operation.
     */
    public function deleteMultiple($key, string ...$keys): bool
    {
        $keys = func_get_args();
        return $this->wrapSimpleErrors(function () use ($keys) {
            return $this->deleteItems($this->normalizeKeyList($keys));
        });
    }

    /**
     * Removes multiple items from the pool.
     */
    public function deleteItems(array $keys): bool
    {
        $output = true;

        foreach ($keys as $key) {
            $key = $this->validateKey($key);
            unset($this->deferred[$key]);

            $this->commit();

            if (!$this->driver->delete($this->namespace, $key)) {
                $output = false;
            }
        }

        return $output;
    }


    /**
     * Get item, if miss, set $key as result of $generator
     */
    public function fetch(string $key, callable $generator)
    {
        $item = $this->getItem($key);

        if ($item instanceof Item && $item->isMiss()) {
            $item->lock();
            $value = $generator($item, $this);
            $item->set($value);
            $item->save();
        }

        return $item->get();
    }


    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     */
    public function set($key, $value, $ttl=null): bool
    {
        $item = $this->wrapSimpleErrors(function () use ($key, $ttl) {
            $item = $this->getItem($key);
            return $item->expiresAfter($ttl);
        });

        $item->set($value);
        return $this->save($item);
    }

    /**
     * Persists a cache item immediately.
     */
    public function save(CacheItemInterface $item): bool
    {
        if (!$item instanceof Item) {
            throw Exceptional::{'InvalidArgument,Psr\\Cache\\InvalidArgumentException'}(
                'Cache items must implement Df\\Core\\Cache\\Item',
                null,
                $item
            );
        }

        return $item->save();
    }

    /**
     * Sets a cache item to be persisted later.
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[$item->getKey()] = $item;
        return true;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     */
    public function setMultiple($values, $ttl=null)
    {
        $values = $this->normalizeKeyList($values);

        return $this->wrapSimpleErrors(function () use ($values, $ttl) {
            $items = $this->getItems(array_keys($values));
            $success = true;

            foreach ($items as $key => $item) {
                $item->set($values[$key]);
                $item->expiresAfter($ttl);
                $success = $success && $this->saveDeferred($item);
            }

            return $success && $this->commit();
        });
    }

    /**
     * Persists any deferred cache items.
     */
    public function commit(): bool
    {
        $output = true;

        foreach ($this->deferred as $key => $item) {
            if (!$item->save()) {
                $output = false;
            }
        }

        $this->deferred = [];
        return $output;
    }


    /**
     * Shortcut set
     */
    public function __set(string $key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Shortcut getItem()
     */
    public function __get(string $key)
    {
        return $this->getItem($key);
    }

    /**
     * Shortcut hasItem()
     */
    public function __isset(string $key): bool
    {
        return $this->hasItem($key);
    }

    /**
     * Shortcut delete item
     */
    public function __unset(string $key)
    {
        $this->deleteItem($key);
    }


    /**
     * Shortcut set()
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Shortcut get()
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Shortcut has()
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Shortcut delete()
     */
    public function offsetUnset($key)
    {
        $this->delete($key);
    }



    /**
     * Set pile up policy to ignore
     */
    public function pileUpIgnore(): Store
    {
        $this->pileUpPolicy = Item::IGNORE;
        return $this;
    }

    /**
     * Set pile up policy to preempt
     */
    public function pileUpPreempt(int $preemptTime=null): Store
    {
        $this->pileUpPolicy = Item::PREEMPT;

        if ($preemptTime !== null) {
            $this->preemptTime = $preemptTime;
        }

        return $this;
    }

    /**
     * Set pile up policy to sleep
     */
    public function pileUpSleep(int $time=null, int $attempts=null): Store
    {
        $this->pileUpPolicy = Item::SLEEP;

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
    public function pileUpValue(): Store
    {
        $this->pileUpPolicy = Item::VALUE;
        return $this;
    }


    /**
     * Set pile up policy
     */
    public function setPileUpPolicy(string $policy): Store
    {
        $this->pileUpPolicy = $policy;
        return $this;
    }

    /**
     * Get pile up policy
     */
    public function getPileUpPolicy(): string
    {
        return $this->pileUpPolicy;
    }


    /**
     * Replace preempt time
     */
    public function setPreemptTime(int $preemptTime): Store
    {
        $this->preemptTime = $preemptTime;
        return $this;
    }

    /**
     * Get preempt time
     */
    public function getPreemptTime(): int
    {
        return $this->preemptTime;
    }


    /**
     * Replace sleep time
     */
    public function setSleepTime(int $time): Store
    {
        $this->sleepTime = $time;
        return $this;
    }

    /**
     * Get sleep time
     */
    public function getSleepTime(): int
    {
        return $this->sleepTime;
    }

    /**
     * Replace sleep attempts
     */
    public function setSleepAttempts(int $attempts): Store
    {
        $this->sleepAttempts = $attempts;
        return $this;
    }

    /**
     * Get sleep attempts
     */
    public function getSleepAttempts(): int
    {
        return $this->sleepAttempts;
    }





    /**
     * Validate single key
     */
    protected function validateKey($key): string
    {
        if (!is_string($key) || !isset($key[0])) {
            throw Exceptional::{'InvalidArgument,Psr\\Cache\\InvalidArgumentException'}(
                'Cache key must be a string',
                null,
                $key
            );
        }

        if (preg_match('|[\{\}\(\)/\\\@\:]|', $key)) {
            throw Exceptional::{'InvalidArgument,Psr\\Cache\\InvalidArgumentException'}(
                'Cache key must not contain reserved extension characters: {}()/\@:',
                null,
                $key
            );
        }

        return $key;
    }

    /**
     * Normalize iterable key list
     */
    protected function normalizeKeyList($keys): array
    {
        if (!is_array($keys)) {
            if (!$keys instanceof \Traversable) {
                throw Exceptional::{'InvalidArgument,Psr\\SimpleCache\\InvalidArgumentException'}(
                    'Invalid cache keys',
                    null,
                    $keys
                );
            }

            $keys = iterator_to_array($keys);
        }

        return $keys;
    }



    /**
     * Wrap simple errors
     */
    protected function wrapSimpleErrors(callable $func)
    {
        try {
            return $func();
        } catch (CacheInvalidArgumentException $e) {
            throw Exceptional::{'InvalidArgument,Psr\\SimpleCache\\InvalidArgumentException'}(
                $e->getMessage(),
                ['previous' => $e]
            );
        }
    }
}
