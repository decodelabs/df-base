<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Cache\Driver;

use Df;
use Df\Core\Cache\IDriver;
use Df\Core\Cache\IItem;
use Df\Core\Config\Repository;

class PhpArray implements IDriver
{
    use TKeyGen;

    const KEY_SEPARATOR = '::';

    protected $values = [];
    protected $locks = [];

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
    public static function fromConfig(Repository $config): ?IDriver
    {
        return new static();
    }

    /**
     * Store item data
     */
    public function store(string $namespace, string $key, $value, int $created, ?int $expires): bool
    {
        $this->values[$this->createKey($namespace, $key)] = [
            $value, $expires
        ];

        return true;
    }

    /**
     * Fetch item data
     */
    public function fetch(string $namespace, string $key): ?array
    {
        return $this->values[$this->createKey($namespace, $key)] ?? null;
    }

    /**
     * Remove item from store
     */
    public function delete(string $namespace, string $key): bool
    {
        $regex = $this->createRegexKey($namespace, $key);

        foreach ($this->values as $key => $value) {
            if (preg_match($regex, $key)) {
                unset($this->values[$key]);
            }
        }

        return true;
    }

    /**
     * Clear all values from store
     */
    public function clearAll(string $namespace): bool
    {
        $regex = $this->createRegexKey($namespace, null);

        foreach ($this->values as $key => $value) {
            if (preg_match($regex, $key)) {
                unset($this->values[$key]);
            }
        }

        unset($this->locks[$namespace]);
        return true;
    }



    /**
     * Save a lock for a key
     */
    public function storeLock(string $namespace, string $key, int $expires): bool
    {
        $this->locks[$namespace][$key] = $expires;
        return true;
    }

    /**
     * Get a lock expiry for a key
     */
    public function fetchLock(string $namespace, string $key): ?int
    {
        return $this->locks[$namespace][$key] ?? null;
    }

    /**
     * Remove a lock
     */
    public function deleteLock(string $namespace, string $key): bool
    {
        unset($this->locks[$namespace][$key]);
        return true;
    }


    /**
     * Delete EVERYTHING in this store
     */
    public function purge(): void
    {
        $this->values = [];
        $this->locks = [];
    }
}
