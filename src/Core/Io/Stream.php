<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Io;

use Df;

class Stream implements IChannel
{
    use TChannel;

    protected $resource;
    protected $readable = null;
    protected $writable = null;

    /**
     * Init with stream path
     */
    public function __construct(string $path, string $mode='a+')
    {
        if (!$this->resource = fopen($path, $mode)) {
            throw Df\Error::EIo('Unable to open stream');
        }
    }

    /**
     * Set read blocking mode
     */
    public function setBlocking(bool $flag): void
    {
        stream_set_blocking($this->resource, $flag);
    }

    /**
     * Is this channel in blocking mode?
     */
    public function isBlocking(): bool
    {
        if (!$this->resource) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        return (bool)$meta['blocked'];
    }

    /**
     * Is the resource still accessible?
     */
    public function isReadable(): bool
    {
        if ($this->resource === null) {
            return false;
        }

        if ($this->readable === null) {
            $meta = stream_get_meta_data($this->resource);
            $mode = $meta['mode'];

            $this->readable = (strstr($mode, 'r') || strstr($mode, '+'));
        }

        return $this->readable;
    }

    /**
     * Read up to $length bytes from resource
     */
    public function read(int $length): ?string
    {
        $this->checkReadable();

        try {
            $output = fread($this->resource, $length);
        } catch (\Throwable $e) {
            return null;
        }

        if ($output === '' || $output === false) {
            $output = null;
        }

        return $output;
    }

    /**
     * Read single line from resource
     */
    public function readLine(): ?string
    {
        $this->checkReadable();

        try {
            $output = fgets($this->resource);
        } catch (\Throwable $e) {
            return null;
        }

        if ($output === '' || $output === false) {
            $output = null;
        } else {
            $output = rtrim($output, "\r\n");
        }

        return $output;
    }

    /**
     * Is the resource still writable?
     */
    public function isWritable(): bool
    {
        if ($this->resource === null) {
            return false;
        }

        if ($this->writable === null) {
            $meta = stream_get_meta_data($this->resource);
            $mode = $meta['mode'];

            $this->writable = (
                strstr($mode, 'x') ||
                strstr($mode, 'w') ||
                strstr($mode, 'c') ||
                strstr($mode, 'a') ||
                strstr($mode, '+')
            );
        }

        return $this->writable;
    }

    /**
     * Write ?$length bytes to resource
     */
    public function write(string $data, int $length=null): int
    {
        $this->checkWritable();

        if ($length !== null) {
            return fwrite($this->resource, $data, $length);
        } else {
            return fwrite($this->resource, $data);
        }
    }

    /**
     * Close the stream
     */
    public function close(): void
    {
        if ($this->resource) {
            try {
                fclose($this->resource);
            } catch (\Throwable $e) {
            }
        }

        $this->resource = null;
        $this->readable = false;
        $this->writable = false;
    }

    /**
     * Get resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
