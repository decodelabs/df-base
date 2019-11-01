<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Cache;

use Df\Core\Config\Repository;

interface Driver
{
    public static function isAvailable(): bool;
    public static function fromConfig(Repository $config): ?Driver;

    public function store(string $namespace, string $key, $value, int $created, ?int $expires): bool;
    public function fetch(string $namespace, string $key): ?array;
    public function delete(string $namespace, string $key): bool;
    public function clearAll(string $namespace): bool;

    public function storeLock(string $namespace, string $key, int $expires): bool;
    public function fetchLock(string $namespace, string $key): ?int;
    public function deleteLock(string $namespace, string $key): bool;

    public function purge(): void;
}
