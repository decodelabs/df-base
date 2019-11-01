<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Cache\Driver;

use Df\Core\Cache\Driver;
use Df\Core\Config\Repository;

class Redis implements Driver
{
    use TIndexedKeyGen;

    const KEY_SEPARATOR = '::';

    protected $redis;

    /**
     * Can this be loaded?
     */
    public static function isAvailable(): bool
    {
        return extension_loaded('redis');
    }

    /**
     * Attempt to load an instance from config
     */
    public static function fromConfig(Repository $config): ?Driver
    {
        if (isset($config->host)) {
            $redis = new \Redis();
            $redis->pconnect($config['host'], $config['port'], $config['timeout']);
            return new static($redis);
        }

        return static::createLocal();
    }

    /**
     * Create a local instance of Memcached
     */
    public static function createLocal(): Driver
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        return new static($redis);
    }

    /**
     * Init with redis Client instance
     */
    public function __construct(\Redis $redis)
    {
        $this->generatePrefix();
        $this->redis = $redis;
    }

    /**
     * Ensure redis is closed
     */
    public function __destruct()
    {
        if ($this->redis instanceof \Redis) {
            try {
                $this->redis->close();
            } catch (\RedisException $e) {
            }
        }
    }


    /**
     * Store item data
     */
    public function store(string $namespace, string $key, $value, int $created, ?int $expires): bool
    {
        if ($expires === null) {
            $ttl = 0;
        } else {
            $ttl = $expires - $created;
        }

        $key = $this->createNestedKey($namespace, $key)[0];
        $data = serialize([$value, $expires]);

        if ($ttl > 0) {
            return $this->redis->setex($key, $ttl, $data);
        } else {
            return $this->redis->set($key, $data);
        }
    }

    /**
     * Fetch item data
     */
    public function fetch(string $namespace, string $key): ?array
    {
        $key = $this->createNestedKey($namespace, $key)[0];
        $output = $this->redis->get($key);

        if (is_string($output)) {
            $output = unserialize($output);
        } else {
            $output = null;
        }

        if (!is_array($output)) {
            $output = null;
        }

        return $output;
    }

    /**
     * Remove item from store
     */
    public function delete(string $namespace, string $key): bool
    {
        $man = $this->parseKey($namespace, $key);
        $key = $this->createNestedKey($namespace, $man['normal']);

        if ($man['self']) {
            $this->redis->delete($key[0]);
        }

        if ($man['children']) {
            if (!$this->redis->incr($key[1])) {
                $this->redis->set($key[1], 1);
            }
        }

        $this->keyCache = [];
        return true;
    }

    /**
     * Clear all values from store
     */
    public function clearAll(string $namespace): bool
    {
        $key = $this->createNestedKey($namespace, null)[1];

        if (!$this->redis->incr($key)) {
            $this->redis->set($key, 1);
        }

        $this->keyCache = [];
        return true;
    }



    /**
     * Save a lock for a key
     */
    public function storeLock(string $namespace, string $key, int $expires): bool
    {
        $key = $this->createLockKey($namespace, $key);
        return $this->redis->setex($key, $expires, $expires - time());
    }

    /**
     * Get a lock expiry for a key
     */
    public function fetchLock(string $namespace, string $key): ?int
    {
        $key = $this->createLockKey($namespace, $key);
        $output = $this->redis->get($key);

        if (!is_int($output)) {
            $output = null;
        }

        return $output;
    }

    /**
     * Remove a lock
     */
    public function deleteLock(string $namespace, string $key): bool
    {
        $key = $this->createLockKey($namespace, $key);
        return (bool)$this->redis->delete($key);
    }



    /**
     * Get cached path index
     */
    protected function getPathIndex(string $pathKey): int
    {
        return (int)$this->redis->get($pathKey);
    }


    /**
     * Delete EVERYTHING in this store
     */
    public function purge(): void
    {
        $this->redis->flushDb();
    }
}
