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

use DecodeLabs\Atlas;
use DecodeLabs\Atlas\File as FileInterface;
use DecodeLabs\Atlas\File\Local as LocalFile;
use DecodeLabs\Atlas\Dir\Local as LocalDir;

class File implements IDriver
{
    use TKeyGen;

    const KEY_SEPARATOR = '/';
    const EXTENSION = '.cache';

    protected $dir;
    protected $dirPerms = 0770;
    protected $filePerms = 0660;

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
        $name = lcfirst((new \ReflectionClass(get_called_class()))->getShortName());
        $path = $config['path'] ?? Df\BASE_PATH.'/storage/local/cache@'.$name;
        $output = new static($path);

        if (isset($config->dirPerms)) {
            $output->setDirPermissions((int)$config['dirPerms']);
        }

        if (isset($config->filePerms)) {
            $output->setFilePerms((int)$config['filePerms']);
        }

        return $output;
    }

    /**
     * Init with base path
     */
    public function __construct(string $dirPath)
    {
        $this->dir = new LocalDir($dirPath);
    }

    /**
     * Set default dir perms
     */
    public function setDirPermissions(int $perms): File
    {
        $this->dirPerms = $perms;
        return $this;
    }

    /**
     * Get default dir perms
     */
    public function getDirPermissions(): int
    {
        return $this->dirPerms;
    }

    /**
     * Set default file perms
     */
    public function setFilePermissions(int $perms): File
    {
        $this->filePerms = $perms;
        return $this;
    }

    /**
     * Get default file perms
     */
    public function getFilePermissions(): int
    {
        return $this->filePerms;
    }




    /**
     * Store item data
     */
    public function store(string $namespace, string $key, $value, int $created, ?int $expires): bool
    {
        $this->dir->ensureExists($this->dirPerms);
        $file = $this->getFile($namespace, $key);
        $data = $this->buildFileContent($file, $namespace, $key, $value, $created, $expires);

        try {
            $file->putContents($data);
            $output = true;
        } catch (Atlas\EGlitch $e) {
            $output = false;
        }

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file->getPath(), true);
        }

        return $output;
    }

    /**
     * Store item data in file
     */
    protected function buildFileContent(FileInterface $file, string $namespace, string $key, $value, int $created, ?int $expires): string
    {
        return serialize([
            'namespace' => $namespace,
            'key' => $key,
            'expires' => $expires,
            'value' => $value
        ]);
    }

    /**
     * Fetch item data
     */
    public function fetch(string $namespace, string $key): ?array
    {
        $file = $this->getFile($namespace, $key);
        $file->clearStatCache();

        if (!$file->exists()) {
            return null;
        }

        if (null === ($data = $this->loadFileContent($file))) {
            return null;
        }

        if ($data['namespace'] !== $namespace || $data['key'] !== $key) {
            return null;
        }

        return [
            $data['value'], $data['expires'] ?? null
        ];
    }

    /**
     * Get item data from file
     */
    protected function loadFileContent(FileInterface $file): ?array
    {
        try {
            $data = unserialize($file->getContents());
        } catch (\Throwable $e) {
            return null;
        }

        if (is_null($data) || !is_array($data)) {
            return null;
        }

        return $data;
    }

    /**
     * Remove item from store
     */
    public function delete(string $namespace, string $key): bool
    {
        $key = $this->inspectKey($namespace, $key);
        $root = $this->hashKey($key['key']);

        if ($key['children']) {
            $this->dir->deleteDir($root);
        }

        if ($key['self']) {
            $this->dir->deleteFile($root.static::EXTENSION);
        }

        return true;
    }

    /**
     * Clear all values from store
     */
    public function clearAll(string $namespace): bool
    {
        $key = $this->inspectKey($namespace, null);
        $root = $this->hashKey($key['key']);
        $this->dir->deleteDir($root);
        return true;
    }



    /**
     * Save a lock for a key
     */
    public function storeLock(string $namespace, string $key, int $expires): bool
    {
        $file = $this->getLockFile($namespace, $key);

        try {
            $file->putContents($expires);
            return true;
        } catch (Atlas\EGlitch $e) {
            return false;
        }
    }

    /**
     * Get a lock expiry for a key
     */
    public function fetchLock(string $namespace, string $key): ?int
    {
        $file = $this->getLockFile($namespace, $key);
        $file->clearStatCache();

        if (!$file->exists()) {
            return null;
        }

        $expires = $file->getContents();

        if ($expires !== null) {
            $expires = (int)$expires;
        }

        return $expires;
    }

    /**
     * Remove a lock
     */
    public function deleteLock(string $namespace, string $key): bool
    {
        $file = $this->getLockFile($namespace, $key);
        $file->delete();
        return true;
    }



    /**
     * Create file path from key
     */
    protected function getFile(string $namespace, string $key): FileInterface
    {
        $key = $this->createKey($namespace, $key);
        $key = $this->hashKey($key).static::EXTENSION;
        return $this->dir->getFile($key);
    }

    /**
     * Create file path from key
     */
    protected function getLockFile(string $namespace, string $key): FileInterface
    {
        $key = $this->createKey($namespace, $key);
        $key = $this->hashKey($key).'.lock';
        return $this->dir->getFile($key);
    }

    /**
     * Hash key parts
     */
    protected function hashKey(string $key): string
    {
        $key = trim($key, '/');
        $parts = explode(static::KEY_SEPARATOR, $key);

        foreach ($parts as &$part) {
            if ($part !== '') {
                $part = md5($part);
            }
        }

        return implode(static::KEY_SEPARATOR, $parts);
    }



    /**
     * Delete EVERYTHING in this store
     */
    public function purge(): void
    {
        $this->dir->delete();
    }
}
