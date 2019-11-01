<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Cache\Driver;

use Df\Core\Cache\Driver;
use Df\Core\Config\Repository;

use Predis\Client;
use Predis\ClientInterface;

class Predis implements Driver
{
    use TIndexedKeyGen;

    const KEY_SEPARATOR = '::';

    protected $predis;

    /**
     * Can this be loaded?
     */
    public static function isAvailable(): bool
    {
        return class_exists(Client::class);
    }

    /**
     * Attempt to load an instance from config
     */
    public static function fromConfig(Repository $config): ?Driver
    {
        if (isset($config->host) || isset($config->path)) {
            return new static(new Client($config->toArray()));
        }

        return static::createLocal();
    }

    /**
     * Create a local instance of Memcached
     */
    public static function createLocal(): Driver
    {
        return new static(new Client());
    }

    /**
     * Init with Predis Client instance
     */
    public function __construct(ClientInterface $predis)
    {
        $this->generatePrefix();
        $this->predis = $predis;
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
            return 'OK' === $this->predis->setex($key, $ttl, $data)->getPayload();
        } else {
            return 'OK' === $this->predis->set($key, $data)->getPayload();
        }
    }

    /**
     * Fetch item data
     */
    public function fetch(string $namespace, string $key): ?array
    {
        $key = $this->createNestedKey($namespace, $key)[0];
        $output = $this->predis->get($key);

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
            $this->predis->del($key[0]);
        }

        if ($man['children']) {
            if (!$this->predis->incr($key[1])) {
                $this->predis->set($key[1], 1);
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

        if (!$this->predis->incr($key)) {
            $this->predis->set($key, 1);
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
        return 'OK' === $this->predis->setex($key, $expires, $expires - time())->getPayload();
    }

    /**
     * Get a lock expiry for a key
     */
    public function fetchLock(string $namespace, string $key): ?int
    {
        $key = $this->createLockKey($namespace, $key);
        $output = $this->predis->get($key);

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
        return (bool)$this->predis->del($key);
    }



    /**
     * Get cached path index
     */
    protected function getPathIndex(string $pathKey): int
    {
        return (int)$this->predis->get($pathKey);
    }



    /**
     * Delete EVERYTHING in this store
     */
    public function purge(): void
    {
        $this->predis->flushdb();
    }
}
