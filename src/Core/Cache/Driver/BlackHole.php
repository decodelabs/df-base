<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Cache\Driver;

use Df\Core\Cache\Driver;
use Df\Core\Config\Repository;

class BlackHole implements Driver
{
    /**
     * Can this be loaded?
     */
    public static function isAvailable(): bool
    {
        return true;
    }

    /**
     * Attempt to load an instance from config
     */
    public static function fromConfig(Repository $config): ?Driver
    {
        return new static();
    }

    /**
     * Store item data
     */
    public function store(string $namespace, string $key, $value, int $created, ?int $expires): bool
    {
        return true;
    }

    /**
     * Fetch item data
     */
    public function fetch(string $namespace, string $key): ?array
    {
        return null;
    }

    /**
     * Remove item from store
     */
    public function delete(string $namespace, string $key): bool
    {
        return true;
    }

    /**
     * Clear all values from store
     */
    public function clearAll(string $namespace): bool
    {
        return true;
    }



    /**
     * Save a lock for a key
     */
    public function storeLock(string $namespace, string $key, int $expires): bool
    {
        return true;
    }

    /**
     * Get a lock expiry for a key
     */
    public function fetchLock(string $namespace, string $key): ?int
    {
        return null;
    }

    /**
     * Remove a lock
     */
    public function deleteLock(string $namespace, string $key): bool
    {
        return true;
    }


    /**
     * Delete EVERYTHING in this store
     */
    public function purge(): void
    {
        // whatever
    }
}
