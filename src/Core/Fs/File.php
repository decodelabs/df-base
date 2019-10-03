<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Fs;

use Df;

use DecodeLabs\Atlas\Channel;
use DecodeLabs\Atlas\Channel\Stream;

class File extends Stream implements IFile
{
    use TNode;

    protected $path;

    /**
     * Init with file path, if mode is set, open file
     */
    public function __construct(string $path, string $mode=null)
    {
        $this->path = $path;

        if ($mode !== null) {
            $this->open($mode);
        }
    }

    /**
     * Ensure file is closed
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Does this file exist on disk?
     */
    public function exists(): bool
    {
        if ($this->resource) {
            return true;
        }

        return file_exists($this->path);
    }

    /**
     * Is the resource still accessible?
     */
    public function isReadable(): bool
    {
        if ($this->resource === null) {
            return is_readable($this->path);
        }

        return parent::isReadable();
    }

    /**
     * Is the resource still writable?
     */
    public function isWritable(): bool
    {
        if ($this->resource === null) {
            return is_writable($this->path);
        }

        return parent::isWritable();
    }


    /**
     * Get size of file in bytes
     */
    public function getSize(): ?int
    {
        if (!$this->exists()) {
            return null;
        }

        return filesize($this->path);
    }

    /**
     * Get hash of file contents
     */
    public function getHash(string $type, bool $raw=false): ?string
    {
        if (!$this->exists()) {
            return null;
        }

        return hash_file($type, $this->path, $raw);
    }


    /**
     * Open file for reading and writing
     */
    public function open(string $mode): IFile
    {
        if ($this->resource !== null) {
            if ($this->mode === $mode) {
                return $this;
            }

            $this->close();
        }

        $this->mode = $mode;

        $isWrite =
            strstr($this->mode, 'x') ||
            strstr($this->mode, 'w') ||
            strstr($this->mode, 'c') ||
            strstr($this->mode, 'a') ||
            strstr($this->mode, '+');

        if ($isWrite && !$this->exists()) {
            $mkDir = true;

            if (false !== strpos($this->path, '://')) {
                $parts = explode('://', $this->path, 2);

                if ($parts[0] !== 'file') {
                    $mkDir = false;
                }
            }

            if ($mkDir) {
                (new Dir(dirname($this->path)))->ensureExists();
            }
        }

        if (!$this->resource = fopen($this->path, $mode)) {
            throw Glitch::EIo('Unable to open file', null, $this);
        }

        return $this;
    }

    /**
     * Has this file been opened?
     */
    public function isOpen(): bool
    {
        return $this->resource !== null;
    }

    /**
     * Is this file a symbolic link?
     */
    public function isLink(): bool
    {
        return is_link($this->path);
    }


    /**
     * Set permissions on file
     */
    public function setPermissions(int $mode): IFile
    {
        if (!$this->exists()) {
            throw Glitch::ENotFound('Cannot set permissions, file does not exist', null, $this);
        }

        chmod($this->path, $mode);
        return $this;
    }

    /**
     * Set owner of file
     */
    public function setOwner(int $owner): IFile
    {
        if (!$this->exists()) {
            throw Glitch::ENotFound('Cannot set owner, file does not exist', null, $this);
        }

        chown($this->path, $owner);
        return $this;
    }

    /**
     * Set group of file
     */
    public function setGroup(int $group): IFile
    {
        if (!$this->exists()) {
            throw Glitch::ENotFound('Cannot set owner, file does not exist', null, $this);
        }

        chgrp($this->path, $group);
        return $this;
    }


    /**
     * Attempt to shared lock file
     */
    public function lock(bool $nonBlocking=false): bool
    {
        if ($this->resource === null) {
            throw Glitch::EIo('Cannot lock file, file not open', null, $this);
        }

        if ($nonBlocking) {
            return flock($this->resource, LOCK_SH | LOCK_NB);
        } else {
            return flock($this->resource, LOCK_SH);
        }
    }

    /**
     * Attempt to exclusive lock file
     */
    public function lockExclusive(bool $nonBlocking=false): bool
    {
        if ($this->resource === null) {
            throw Glitch::EIo('Cannot lock file, file not open', null, $this);
        }

        if ($nonBlocking) {
            return flock($this->resource, LOCK_EX | LOCK_NB);
        } else {
            return flock($this->resource, LOCK_EX);
        }
    }

    /**
     * Unlock file
     */
    public function unlock(): IFile
    {
        if ($this->resource === null) {
            return $this;
        }

        if (!flock($this->resource, LOCK_UN)) {
            throw Glitch::EIo('Unable to unlock file', null, $this);
        }

        return $this;
    }


    /**
     * Write content to file
     */
    public function putContents($data): IFile
    {
        $closeData = $closeAfter = false;

        if (!$data instanceof Channel) {
            $file = new File('php://temp', 'w+');
            $file->write((string)$data);
            $file->seek(0);
            $data = $file;
            $closeData = true;
        }

        if ($data instanceof IFile && !$data->isOpen()) {
            $data->open('r');
            $closeData = true;
        }

        if ($this->resource === null) {
            $closeAfter = true;
            $this->open('w');
        }

        if (!$this->lockExclusive()) {
            throw Glitch::EIo('Unable to lock file for writing', null, $this);
        }

        $this->truncate();
        $data->writeTo($this);
        $this->unlock();

        if ($closeAfter) {
            $this->close();
        }

        if ($closeData) {
            $data->close();
        }

        return $this;
    }

    /**
     * Read contents of file
     */
    public function getContents(): string
    {
        $closeAfter = false;

        if ($this->resource === null) {
            $closeAfter = true;
            $this->open('r');
        }

        if (!$this->lock()) {
            throw Glitch::EIo('Unable to lock file for reading', null, $this);
        }

        $this->seek(0);
        $output = (string)$this->readAll();
        $this->unlock();

        if ($closeAfter) {
            $this->close();
        }

        return $output;
    }


    /**
     * Copy file to new location
     */
    public function copyTo(string $path): INode
    {
        $target = new self($path, 'w');
        $closeAfter = false;

        if ($this->resource === null) {
            $closeAfter = true;
            $this->open('r');
        }

        $this->lock();
        $this->seek(0);

        while (!$this->isAtEnd()) {
            $target->write($this->read(8192));
        }

        $this->unlock();

        if ($closeAfter) {
            $this->close();
        }

        $target->close();
        return $target;
    }

    /**
     * Move file to new location
     */
    public function moveTo(string $destination, string $newName=null): INode
    {
        if (!$this->exists()) {
            throw Glitch::ENotFound('Source file does not exist', null, $this);
        }

        if ($newName === null) {
            $newName = basename($this->path);
        }

        if ($newName == '' || $newName === '..' || $newName === '.' || strstr($newName, '/')) {
            throw Glitch::EInvalidArgument('New file name is invalid: '.$name, null, $this);
        }

        $destination = rtrim($destination, '/').'/'.$newName;
        (new Dir(dirname($destination)))->ensureExists();

        if (file_exists($destination)) {
            throw Glitch::EIo('Destination file already exists', null, $destination);
        }

        if (!rename($this->path, $destination)) {
            throw Glitch::EIo('Unable to rename file', null, $this);
        }

        $this->path = $destination;
        return $this;
    }

    /**
     * Delete file from filesystem
     */
    public function delete(): void
    {
        $exists = $this->exists();
        $this->close();

        if ($exists) {
            try {
                unlink($this->path);
            } catch (\Throwable $e) {
                if ($this->exists()) {
                    throw $e;
                }
            }
        }
    }


    /**
     * Seek file pointer to offset
     */
    public function seek(int $offset, int $whence=SEEK_SET): IFile
    {
        if ($this->resource === null) {
            throw Glitch::EIo('Cannot seek file, file not open', null, $this);
        }

        if (0 !== fseek($this->resource, $offset, $whence)) {
            throw Glitch::EIo('Failed to seek file', null, $this);
        }

        return $this;
    }

    /**
     * Get location of file pointer
     */
    public function tell(): int
    {
        if ($this->resource === null) {
            throw Glitch::EIo('Cannot ftell file, file not open', null, $this);
        }

        $output = ftell($this->resource);

        if ($output === false) {
            throw Glitch::EIo('Failed to ftell file', null, $this);
        }

        return $output;
    }

    /**
     * Ensure all data is written to file
     */
    public function flush(): IFile
    {
        if ($this->resource === null) {
            throw Glitch::EIo('Cannot flush file, file not open', null, $this);
        }

        $output = fflush($this->resource);

        if ($output === false) {
            throw Glitch::EIo('Failed to flush file', null, $this);
        }

        return $output;
    }

    /**
     * Truncate a file to $size bytes
     */
    public function truncate(int $size=0): IFile
    {
        if ($this->resource !== null) {
            ftruncate($this->resource, $size);
        } else {
            $this->open('w');
            $this->close();
        }

        return $this;
    }
}
