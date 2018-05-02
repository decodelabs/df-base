<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\http\body;

use df;
use df\http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Generator implements StreamInterface
{
    protected $iterator;
    protected $buffer = '';
    protected $position = 0;
    protected $eof = false;
    protected $complete = false;
    protected $bufferAll = true;

    /**
     * Init with iterator
     */
    public function __construct($iterator, bool $buffer=true)
    {
        if (!$iterator instanceof \Iterator && is_callable($iterator)) {
            $iterator = $iterator();
        }

        if (!$iterator instanceof \Iterator) {
            throw df\Error::EInvalidArgument(
                'Invalid iterator passed as response'
            );
        }

        $this->iterator = $iterator;
        $this->bufferAll = $buffer;
    }

    /**
     * Get size if available
     */
    public function getSize(): ?int
    {
        return null;
    }

    /**
     * Get position of iterator
     */
    public function tell(): int
    {
        return $this->position;
    }

    /**
     * Are we at the end?
     */
    public function eof(): bool
    {
        return $this->eof;
    }

    /**
     * Can we seek this stream?
     */
    public function isSeekable(): bool
    {
        return false;
    }

    /**
     * Try and seek to position
     */
    public function seek($offset, $whence=SEEK_SET): void
    {
        throw df\Error::ERuntime('Iterators cannot seek');
    }

    /**
     * Try and rewind
     */
    public function rewind(): void
    {
        throw df\Error::ERuntime('Iterators cannot seek');
    }

    /**
     * Can we write to this?
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * Try and write to stream
     */
    public function write($string): void
    {
        throw df\Error::ERuntime('Iterators cannot be written to');
    }

    /**
     * Can we read from this stream?
     */
    public function isReadable(): bool
    {
        return !$this->eof;
    }

    /**
     * Read from stream
     * If $bufferAll, the iterator is iterated until the buffer can send $length
     * otherise it's iterated once on each call
     */
    public function read($length): string
    {
        if ($this->iterator === null) {
            throw df\Error::ERuntime('Cannot read from stream, resource has been detached');
        }

        if ($this->eof) {
            throw df\Error::ERuntime('Cannot read from stream, iterator has completed');
        }


        $length = (int)$length;

        if (!$this->complete && strlen($this->buffer) < $length) {
            do {
                $this->buffer .= (string)$this->iterator->current();
                $this->iterator->next();

                if (!$this->iterator->valid()) {
                    $this->complete = true;
                    break;
                }
            } while ($this->bufferAll && strlen($this->buffer) < $length);
        }

        $output = substr($this->buffer, 0, $length);

        if (!empty($this->buffer)) {
            $this->buffer = substr($this->buffer, $outLength = strlen($output));
        }

        $this->position += $outLength;

        if ($this->complete && empty($this->buffer)) {
            $this->eof = true;
        }

        return $output;
    }

    /**
     * Get remaining contents from stream
     */
    public function getContents(): string
    {
        if ($this->iterator === null) {
            throw df\Error::ERuntime('Cannot read from stream, resource has been detached');
        }

        if ($this->eof) {
            throw df\Error::ERuntime('Cannot read from stream, iterator has completed');
        }

        $output = '';

        while (!$this->eof) {
            $output .= $this->read(4096);
        }

        $this->eof = true;

        return $output;
    }

    /**
     * Get stream metadata
     */
    public function getMetadata($key=null)
    {
        $metadata = [
            'eof' => $this->eof(),
            'stream_type' => 'iterator',
            'seekable' => false
        ];

        if (null === $key) {
            return $metadata;
        }

        if (!array_key_exists($key, $metadata)) {
            return null;
        }

        return $metadata[$key];
    }

    /**
     * Close the stream
     */
    public function close(): void
    {
        if (!$this->iterator) {
            return;
        }

        $this->detach();
    }

    /**
     * Detach iterator from stream
     */
    public function detach()
    {
        $output = $this->iterator;
        $this->iterator = null;

        return $output;
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        if (!$this->isReadable()) {
            return '';
        }

        try {
            return $this->getContents();
        } catch (\Throwable $e) {
            return '';
        }
    }
}
