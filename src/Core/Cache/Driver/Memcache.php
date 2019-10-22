<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Cache\Driver;

use Df\Core\Cache\IDriver;
use Df\Core\Config\Repository;

class Memcache implements IDriver
{
    use TIndexedKeyGen;

    const KEY_SEPARATOR = '::';

    protected $memcached;

    /**
     * Can this be loaded?
     */
    public static function isAvailable(): bool
    {
        return extension_loaded('memcached');
    }

    /**
     * Attempt to load an instance from config
     */
    public static function fromConfig(Repository $config): ?IDriver
    {
        if (isset($config->servers) && !$config->servers->isEmpty()) {
            return self::create($config->servers->toArray());
        }

        return self::createLocal();
    }

    /**
     * Create a local instance of Memcached
     */
    public static function createLocal(): IDriver
    {
        $memcached = new \Memcached();
        $memcached->addServer('127.0.0.1', 11211);
        return new static($memcached);
    }

    /**
     * Create instance of Memcached from server list
     */
    public static function create(array $servers): IDriver
    {
        $memcached = new \Memcached();
        $memcached->addServers($servers);
        return new static($memcached);
    }


    /**
     * Init with \Memcached instance
     */
    public function __construct(\Memcached $memcached)
    {
        $this->generatePrefix();
        $this->memcached = $memcached;
    }


    /**
     * Store item data
     */
    public function store(string $namespace, string $key, $value, int $created, ?int $expires): bool
    {
        $key = $this->createNestedKey($namespace, $key)[0];
        return $this->memcached->set($key, [$value, $expires], $expires);
    }

    /**
     * Fetch item data
     */
    public function fetch(string $namespace, string $key): ?array
    {
        $key = $this->createNestedKey($namespace, $key)[0];
        $output = $this->memcached->get($key);

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
            $this->memcached->delete($key[0]);
        }

        if ($man['children']) {
            if (!$this->memcached->increment($key[1])) {
                $this->memcached->set($key[1], 1);
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

        if (!$this->memcached->increment($key)) {
            $this->memcached->set($key, 1);
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
        return $this->memcached->set($key, $expires, $expires);
    }

    /**
     * Get a lock expiry for a key
     */
    public function fetchLock(string $namespace, string $key): ?int
    {
        $key = $this->createLockKey($namespace, $key);
        $output = $this->memcached->get($key);

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
        return $this->memcached->delete($key);
    }



    /**
     * Get cached path index
     */
    protected function getPathIndex(string $pathKey): int
    {
        return (int)$this->memcached->get($pathKey);
    }



    /**
     * Delete EVERYTHING in this store
     */
    public function purge(): void
    {
        $this->memcached->flush();
    }
}
