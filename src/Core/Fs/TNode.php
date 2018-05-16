<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Fs;

use Df;

trait TNode
{
    protected $path;

    /**
     * Get basename of item
     */
    public function getName(): string
    {
        return basename($this->getPath());
    }

    /**
     * Get fs path to node
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Clear stat cache for file / dir
     */
    public function clearStatCache(): INode
    {
        clearstatcache(true, $this->getPath());
        return $this;
    }

    /**
     * Get mtime of file
     */
    public function getLastModified(): ?int
    {
        if (!$this->exists()) {
            return null;
        }

        return filemtime($this->path);
    }

    /**
     * Compare last modified
     */
    public function hasChanged(int $seconds=30): bool
    {
        if (!$this->exists()) {
            return false;
        }

        return time() - $this->getLastModified() < $seconds;
    }

    /**
     * Get permissions of node
     */
    public function getPermissions(): ?int
    {
        if (!$this->exists()) {
            return null;
        }

        return fileperms($this->getPath());
    }

    /**
     * Get owner of node
     */
    public function getOwner(): ?int
    {
        if (!$this->exists()) {
            return null;
        }

        return fileowner($this->getPath());
    }

    /**
     * Get group of node
     */
    public function getGroup(): ?int
    {
        if (!$this->exists()) {
            return null;
        }

        return filegroup($this->getPath());
    }

    /**
     * Rename file within current dir
     */
    public function renameTo(string $newName): INode
    {
        return $this->moveTo(dirname($this->path), $newName);
    }
    
    /**
     * Get path as string
     */
    public function __toString(): string
    {
        return $this->getPath();
    }
}
