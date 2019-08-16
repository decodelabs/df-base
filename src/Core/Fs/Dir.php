<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Fs;

use Df;

class Dir implements IDir
{
    use TNode;

    protected $path;

    /**
     * Init with path
     */
    public function __construct(string $path)
    {
        $this->path = rtrim($path, '/');
    }

    /**
     * Does this dir exist?
     */
    public function exists(): bool
    {
        return is_dir($this->path);
    }

    /**
     * Create dir if it doesn't exist
     */
    public function ensureExists(int $permissions=null): IDir
    {
        if (!is_dir($this->path)) {
            if (file_exists($this->path)) {
                throw \Glitch::EIo('Dir destination exists as file', null, $this);
            }

            if ($permissions === null) {
                $permissions = 0777;
            }

            if (!mkdir($this->path, $permissions, true)) {
                throw \Glitch::EIo('Unable to mkdir', null, $this);
            }
        } else {
            if ($permissions !== null) {
                chmod($this->path, $permissions);
            }
        }

        return $this;
    }

    /**
     * Does this dir contain anything?
     */
    public function isEmpty(): bool
    {
        if (!$this->exists()) {
            return true;
        }

        foreach (new \DirectoryIterator($this->path) as $item) {
            if ($item->isDot()) {
                continue;
            }

            if ($item->isFile() || $item->isLink() || $item->isDir()) {
                return false;
            }
        }

        return true;
    }


    /**
     * Set permission on dir and children if $recursive
     */
    public function setPermissions(int $mode, bool $recursive=false): IDir
    {
        if (!$this->exists()) {
            throw \Glitch::ENotFound('Cannot set permissions, dir does not exist', null, $this);
        }

        chmod($this->path, $mode);

        if ($recursive) {
            foreach ($this->rawScan(true, true) as $item) {
                if ($item instanceof IDir) {
                    $item->setPermissions($mode, true);
                } else {
                    $item->setPermissions($mode);
                }
            }
        }

        return $this;
    }

    /**
     * Set owner on dir and children if $recursive
     */
    public function setOwner(int $owner, bool $recursive=false): IDir
    {
        if (!$this->exists()) {
            throw \Glitch::ENotFound('Cannot set owner, dir does not exist', null, $this);
        }

        chown($this->path, $owner);

        if ($recursive) {
            foreach ($this->rawScan(true, true) as $item) {
                if ($item instanceof IDir) {
                    $item->setOwner($owner, true);
                } else {
                    $item->setOwner($owner);
                }
            }
        }

        return $this;
    }

    /**
     * Set group on dir and children if $recursive
     */
    public function setGroup(int $group, bool $recursive=false): IDir
    {
        if (!$this->exists()) {
            throw \Glitch::ENotFound('Cannot set group, dir does not exist', null, $this);
        }

        chgrp($this->path, $group);

        if ($recursive) {
            foreach ($this->rawScan(true, true) as $item) {
                if ($item instanceof IDir) {
                    $item->setGroup($group, true);
                } else {
                    $item->setGroup($group);
                }
            }
        }

        return $this;
    }


    /**
     * Scan all children as File or Dir objects
     */
    public function scan(callable $filter=null): \Generator
    {
        return $this->scanRaw(true, true, $filter, true);
    }

    /**
     * Scan all children as names
     */
    public function scanNames(callable $filter=null): \Generator
    {
        return $this->scanRaw(true, true, $filter, false);
    }

    /**
     * Count all children
     */
    public function countContents(callable $filter=null): int
    {
        return $this->countGenerator($this->scanRaw(true, true, $filter, null));
    }


    /**
     * Scan all files as File objects
     */
    public function scanFiles(callable $filter=null): \Generator
    {
        return $this->scanRaw(true, false, $filter, true);
    }

    /**
     * Scan all files as names
     */
    public function scanFileNames(callable $filter=null): \Generator
    {
        return $this->scanRaw(true, false, $filter, false);
    }

    /**
     * Count all files
     */
    public function countFiles(callable $filter=null): int
    {
        return $this->countGenerator($this->scanRaw(true, false, $filter, null));
    }


    /**
     * Scan all dirs as Dir objects
     */
    public function scanDirs(callable $filter=null): \Generator
    {
        return $this->scanRaw(false, true, $filter, true);
    }

    /**
     * Scan all dirs as names
     */
    public function scanDirNames(callable $filter=null): \Generator
    {
        return $this->scanRaw(false, true, $filter, false);
    }

    /**
     * Count all dirs
     */
    public function countDirs(callable $filter=null): int
    {
        return $this->countGenerator($this->scanRaw(false, true, $filter, null));
    }

    /**
     * Raw scan generator
     */
    protected function scanRaw(bool $files, bool $dirs, callable $filter=null, ?bool $wrap=true): \Generator
    {
        if (!$this->exists()) {
            return;
        }

        foreach (new \DirectoryIterator($this->path) as $item) {
            if ($item->isDot()) {
                continue;
            } elseif ($item->isDir()) {
                if (!$dirs) {
                    continue;
                }

                $output = $item->getPathname();

                if ($wrap) {
                    $output = new self($output);
                }
            } elseif ($item->isFile() || $item->isLink()) {
                if (!$files) {
                    continue;
                }

                $output = $item->getPathname();

                if ($wrap) {
                    $output = new File($output);
                }
            } else {
                continue;
            }

            $key = $item->getFilename();

            if ($filter && !$filter($key, $output)) {
                continue;
            }

            if ($wrap === null) {
                yield $key;
            } else {
                yield $key => $output;
            }
        }
    }





    /**
     * Scan all children recursively as File or Dir objects
     */
    public function scanRecursive(callable $filter=null): \Generator
    {
        return $this->scanRawRecursive(true, true, $filter, true);
    }

    /**
     * Scan all children recursively as names
     */
    public function scanNamesRecursive(callable $filter=null): \Generator
    {
        return $this->scanRawRecursive(true, true, $filter, false);
    }

    /**
     * Count all children recursively
     */
    public function countContentsRecursive(callable $filter=null): int
    {
        return $this->countGenerator($this->scanRawRecursive(true, true, $filter, null));
    }


    /**
     * Scan all files recursively as File objects
     */
    public function scanFilesRecursive(callable $filter=null): \Generator
    {
        return $this->scanRawRecursive(true, false, $filter, true);
    }

    /**
     * Scan all files recursively as names
     */
    public function scanFileNamesRecursive(callable $filter=null): \Generator
    {
        return $this->scanRawRecursive(true, false, $filter, false);
    }

    /**
     * Count all files recursively
     */
    public function countFilesRecursive(callable $filter=null): int
    {
        return $this->countGenerator($this->scanRawRecursive(true, false, $filter, null));
    }


    /**
     * Scan all dirs recursively as Dir objects
     */
    public function scanDirsRecursive(callable $filter=null): \Generator
    {
        return $this->scanRawRecursive(false, true, $filter, true);
    }

    /**
     * Scan all dirs recursively as names
     */
    public function scanDirNamesRecursive(callable $filter=null): \Generator
    {
        return $this->scanRawRecursive(false, true, $filter, false);
    }

    /**
     * Count all dirs recursively
     */
    public function countDirsRecursive(callable $filter=null): int
    {
        return $this->countGenerator($this->scanRawRecursive(false, true, $filter, null));
    }


    /**
     * Raw recursive scan generator
     */
    protected function scanRawRecursive(bool $files, bool $dirs, callable $filter=null, ?bool $wrap=true): \Generator
    {
        if (!$this->exists()) {
            return;
        }

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->path,
                \FilesystemIterator::KEY_AS_PATHNAME |
                \FilesystemIterator::CURRENT_AS_SELF |
                \FilesystemIterator::SKIP_DOTS
            ),
            $dirs ?
                \RecursiveIteratorIterator::SELF_FIRST :
                \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($it as $item) {
            if ($item->isDot()) {
                continue;
            } elseif ($item->isDir()) {
                if (!$dirs) {
                    continue;
                }

                $output = $item->getPathname();

                if ($wrap) {
                    $output = new self($output);
                }
            } elseif ($item->isFile() || $item->isLink()) {
                if (!$files) {
                    continue;
                }

                $output = $item->getPathname();

                if ($wrap) {
                    $output = new File($output);
                }
            } else {
                continue;
            }

            $key = $item->getSubPathname();

            if ($filter && !$filter($key, $output)) {
                continue;
            }

            if ($wrap === null) {
                yield $key;
            } else {
                yield $key => $output;
            }
        }
    }


    /**
     * Get count of generator yields
     */
    protected function countGenerator(\Generator $generator): int
    {
        $output = 0;

        foreach ($generator as $item) {
            $output++;
        }

        return $output;
    }




    /**
     * Get parent Dir object
     */
    public function getParent(): ?IDir
    {
        if (($path = dirname($this->path)) == $this->path) {
            return null;
        }

        return new self($path);
    }

    /**
     * Get a child File or Dir if it exists
     */
    public function getChild(string $name): ?INode
    {
        $path = $this->path.'/'.ltrim($name, '/');

        if (is_dir($path)) {
            return new self($path);
        } elseif (is_file($path) || is_link($path)) {
            return new File($path);
        }

        return null;
    }

    /**
     * Is there an existing child by $name?
     */
    public function hasChild(string $name): bool
    {
        $path = $this->path.'/'.ltrim($name, '/');
        return file_exists($path);
    }

    /**
     * Ensure a child item is deleted
     */
    public function deleteChild(string $name): INode
    {
        if ($child = $this->getChild($child)) {
            $child->delete();
        }

        return $this;
    }


    /**
     * Create a dir as a child
     */
    public function createDir(string $name, int $permissions=null): IDir
    {
        return $this->getDir($name)->ensureExists($permissions);
    }

    /**
     * Does child dir exist?
     */
    public function hasDir(string $name): bool
    {
        return $this->getDir($name)->exists();
    }

    /**
     * Get a child dir
     */
    public function getDir(string $name, bool $ifExists=false): ?IDir
    {
        $output = new self($this->path.'/'.ltrim($name, '/'));

        if ($ifExists && !$output->exists()) {
            $output = null;
        }

        return $output;
    }

    /**
     * Delete child if its a dir
     */
    public function deleteDir(string $name): IDir
    {
        if ($dir = $this->getDir($name, true)) {
            $dir->delete();
        }

        return $this;
    }


    /**
     * Create a file with content
     */
    public function createFile(string $name, string $content): IFile
    {
        return $this->getFile($name)->putContents($content);
    }

    /**
     * Open a child file
     */
    public function openFile(string $name, string $mode): IFile
    {
        return $this->getFile($name)->open($mode);
    }

    /**
     * Does child file exist?
     */
    public function hasFile(string $name): bool
    {
        return $this->getFile($name)->exists();
    }

    /**
     * Get a child file
     */
    public function getFile(string $name, bool $ifExists=false): ?IFile
    {
        $output = new File($this->path.'/'.ltrim($name, '/'));

        if ($ifExists && !$output->exists()) {
            $output = null;
        }

        return $output;
    }

    /**
     * Delete child if its a file
     */
    public function deleteFile(string $name): IDir
    {
        if ($file = $this->getFile($name, true)) {
            $file->delete();
        }

        return $this;
    }


    /**
     * Copy dir into another dir
     */
    public function copyTo(string $path): INode
    {
        if (file_exists($path)) {
            throw \Glitch::EIo('Destination dir already exists', null, $this);
        }

        return $this->mergeInto($path);
    }

    /**
     * Move dir to a new location
     */
    public function moveTo(string $destination, string $newName=null): INode
    {
        if (!$this->exists()) {
            throw \Glitch::ENotFound('Source dir does not exist', null, $this);
        }

        if ($newName === null) {
            $newName = basename($this->path);
        }

        if ($newName == '' || $newName === '..' || $newName === '.' || strstr($newName, '/')) {
            throw \Glitch::EInvalidArgument('New dir name is invalid: '.$name, null, $this);
        }

        $destination = rtrim($destination, '/').'/'.$newName;
        (new Dir(dirname($destination)))->ensureExists();

        if (file_exists($destination)) {
            throw \Glitch::EIo('Destination file already exists', null, $destination);
        }

        if (!rename($this->path, $destination)) {
            throw \Glitch::EIo('Unable to rename dir', null, $this);
        }

        $this->path = $destination;
        return $this;
    }

    /**
     * Recursively delete dir and its children
     */
    public function delete(): void
    {
        if (!$this->exists()) {
            return;
        }

        foreach ($this->scanRaw(true, true) as $item) {
            $item->delete();
        }

        rmdir($this->path);
    }

    /**
     * Recursively delete all children
     */
    public function emptyOut(): IDir
    {
        if (!$this->exists()) {
            return $this;
        }

        foreach ($this->scanRaw(true, true) as $item) {
            $item->delete();
        }

        return $this;
    }

    /**
     * Merge this dir and its contents into another dir
     */
    public function mergeInto(string $destination): IDir
    {
        if (!$this->exists()) {
            throw \Glitch::ENotFound('Source dir does not exist', null, $this);
        }

        $destination = new Dir($destination);
        $destination->ensureExists($this->getPermissions());

        foreach ($this->scanRawRecursive(true, true) as $subPath => $item) {
            if ($item instanceof IDir) {
                $destination->createDir($subPath, $item->getPermissions());
            } else {
                $item->copyTo($destination->getPath().'/'.$subPath)
                    ->setPermissions($item->getPermissions());
            }
        }

        return $this;
    }
}
