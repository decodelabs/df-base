<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

use DecodeLabs\Deliverance\Channel\Stream as IoStream;
use DecodeLabs\Exceptional;

class Stream implements StreamInterface
{
    protected $resource;

    /**
     * Create temp stream
     */
    public static function createTemp(string $mode='r+'): StreamInterface
    {
        return new static('php://temp', $mode);
    }

    /**
     * Create memory stream
     */
    public static function createMemory(string $mode='r+'): StreamInterface
    {
        return new static('php://memory', $mode);
    }

    /**
     * Create from string
     */
    public static function fromString(string $content, string $mode='r+'): StreamInterface
    {
        $output = self::createTemp($mode);
        $output->write($content);
        $output->rewind();

        return $output;
    }


    /**
     * Instantiate with active stream resource or string
     */
    public function __construct($stream, string $mode='r')
    {
        $this->attach($stream, $mode);
    }


    /**
     * Get size of resource if available
     */
    public function getSize(): ?int
    {
        if ($this->resource === null) {
            return null;
        }

        $stats = fstat($this->resource);

        if ($stats === false) {
            return null;
        }

        return (int)$stats['size'];
    }

    /**
     * Get current position of file pointer
     */
    public function tell(): int
    {
        if ($this->resource === null) {
            throw Exceptional::Runtime(
                'Cannot tell stream position, resource has been detached'
            );
        }

        if (false === ($output = ftell($this->resource))) {
            throw Exceptional::Runtime(
                'Unable to tell stream position'
            );
        }

        return $output;
    }

    /**
     * Return true if at end of resource
     */
    public function eof(): bool
    {
        if ($this->resource === null) {
            return true;
        }

        return feof($this->resource);
    }

    /**
     * Can this stream been seeked?
     */
    public function isSeekable(): bool
    {
        if ($this->resource === null) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        return (bool)$meta['seekable'];
    }

    /**
     * Seek to new position
     */
    public function seek($offset, $whence=SEEK_SET): void
    {
        if ($this->resource === null) {
            throw Exceptional::Runtime(
                'Cannot seek stream position, resource has been detached'
            );
        }

        if (!$this->isSeekable()) {
            throw Exceptional::Runtime(
                'Stream is not seekable'
            );
        }

        $result = fseek($this->resource, $offset, $whence);

        if ($result !== 0) {
            throw Exceptional::Runtime(
                'Stream seeking failed'
            );
        }
    }

    /**
     * Seek to beginning
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * Can this stream be written to?
     */
    public function isWritable(): bool
    {
        if ($this->resource === null) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];

        return (
            strstr($mode, 'x') ||
            strstr($mode, 'w') ||
            strstr($mode, 'c') ||
            strstr($mode, 'a') ||
            strstr($mode, '+')
        );
    }

    /**
     * Write data to the stream
     */
    public function write($string)
    {
        if ($this->resource === null) {
            throw Exceptional::Runtime(
                'Cannot write to stream, resource has been detached'
            );
        }

        if (!$this->isWritable()) {
            throw Exceptional::Runtime(
                'Stream is not writable'
            );
        }

        $output = fwrite($this->resource, $string);

        if ($output === false) {
            throw Exceptional::Runtime(
                'Writing to stream failed'
            );
        }

        return (int)$output;
    }

    /**
     * Can this stream be read?
     */
    public function isReadable(): bool
    {
        if ($this->resource === null) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];

        return (strstr($mode, 'r') || strstr($mode, '+'));
    }

    /**
     * Read from the stream
     */
    public function read($length): string
    {
        if ($this->resource === null) {
            throw Exceptional::Runtime(
                'Cannot read from stream, resource has been detached'
            );
        }

        if (!$this->isReadable()) {
            throw Exceptional::Runtime(
                'Stream is not readable'
            );
        }

        $output = fread($this->resource, $length);

        if ($output === false) {
            throw Exceptional::Runtime(
                'Reading from stream failed'
            );
        }

        return $output;
    }

    /**
     * Get remaining content from stream
     */
    public function getContents(): string
    {
        if (!$this->isReadable()) {
            throw Exceptional::Runtime(
                'Stream is not readable'
            );
        }

        $output = stream_get_contents($this->resource);

        if ($output === false) {
            throw Exceptional::Runtime(
                'Reading from stream failed'
            );
        }

        return $output;
    }

    /**
     * Get stream resource metadata
     */
    public function getMetadata($key=null)
    {
        $output = stream_get_meta_data($this->resource);

        if ($key === null) {
            return $output;
        }

        if (!array_key_exists($key, $output)) {
            return null;
        }

        return $output[$key];
    }


    /**
     * Close the active resource
     */
    public function close(): void
    {
        if (!$this->resource) {
            return;
        }

        $resource = $this->detach();
        fclose($resource);
    }


    /**
     * Ensure stream resource is valid
     */
    public function attach($stream, string $mode='r'): StreamInterface
    {
        if (is_string($stream)) {
            try {
                $stream = fopen($stream, $mode);
            } catch (\ErrorException $e) {
                throw Exceptional::InvalidArgument([
                    'message' => 'Invalid HTTP body stream',
                    'data' => $stream,
                    'previous' => $e
                ]);
            }
        } elseif ($stream instanceof IoStream) {
            $stream = $stream->getResource();
        }

        if (!is_resource($stream)) {
            throw Exceptional::InvalidArgument(
                'Invalid HTTP body stream',
                null,
                $stream
            );
        }

        $this->resource = $stream;
        return $this;
    }

    /**
     * Detaches the resource from the stream
     */
    public function detach()
    {
        $output = $this->resource;
        $this->resource = null;

        return $output;
    }


    /**
     * Get all content as a string
     */
    public function __toString(): string
    {
        if (!$this->isReadable()) {
            return '';
        }

        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }

            return $this->getContents();
        } catch (\Throwable $e) {
            return '';
        }
    }
}
