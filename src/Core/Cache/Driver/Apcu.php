<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Cache\Driver;

use Df;
use Df\Core\Cache\IDriver;
use Df\Core\Config\Repository;

class Apcu implements IDriver
{
    use TKeyGen;

    const KEY_SEPARATOR = '::';

    /**
     * Can this be loaded?
     */
    public static function isAvailable(): bool
    {
        return extension_loaded('apcu');
    }

    /**
     * Attempt to load an instance from config
     */
    public static function fromConfig(Repository $config): ?IDriver
    {
        return new self();
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

        return apcu_store(
            $this->createKey($namespace, $key),
            [$value, $expires],
            $ttl
        );
    }

    /**
     * Fetch item data
     */
    public function fetch(string $namespace, string $key): ?array
    {
        $success = null;

        $output = apcu_fetch(
            $this->createKey($namespace, $key),
            $success
        );

        return $success ? $output : null;
    }

    /**
     * Remove item from store
     */
    public function delete(string $namespace, string $key): bool
    {
        do {
            $empty = true;

            $it = new \APCUIterator(
                $this->createRegexKey($namespace, $key),
                APC_ITER_KEY,
                100
            );

            foreach ($it as $item) {
                $empty = false;
                apcu_delete($item['key']);
            }
        } while (!$empty);

        return true;
    }

    /**
     * Clear all values from store
     */
    public function clearAll(string $namespace): bool
    {
        do {
            $empty = true;

            $it = new \APCUIterator(
                $this->createRegexKey($namespace, null),
                APC_ITER_KEY,
                100
            );

            foreach ($it as $item) {
                $empty = false;
                apcu_delete($item['key']);
            }
        } while (!$empty);

        return true;
    }



    /**
     * Save a lock for a key
     */
    public function storeLock(string $namespace, string $key, int $expires): bool
    {
        return apcu_store(
            $this->createLockKey($namespace, $key),
            $expires,
            $expires - time()
        );
    }

    /**
     * Get a lock expiry for a key
     */
    public function fetchLock(string $namespace, string $key): ?int
    {
        $success = null;

        $output = apcu_fetch(
            $this->createLockKey($namespace, $key),
            $success
        );

        return $success ? $output : null;
    }

    /**
     * Remove a lock
     */
    public function deleteLock(string $namespace, string $key): bool
    {
        $key = $this->createLockKey($namespace, $key);
        return apcu_delete($key);
    }


    /**
     * Delete EVERYTHING in this store
     */
    public function purge(): void
    {
        apcu_clear_cache();
    }
}
