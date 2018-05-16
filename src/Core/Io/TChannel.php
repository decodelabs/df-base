<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Io;

use Df;

trait TChannel
{
    /**
     * Set read blocking mode
     */
    public function setBlocking(bool $flag): void
    {
        if (!$flag) {
            throw Df\Error::ERuntime('Channel does not support non-blocking mode');
        }
    }

    /**
     * Is this channel in blocking mode?
     */
    public function isBlocking(): bool
    {
        return true;
    }


    /**
     * Is the resource still accessible?
     */
    public function isReadable(): bool
    {
        return true;
    }


    /**
     * Read all available data from resource
     */
    public function readAll(): ?string
    {
        $this->checkReadable();
        $data = null;

        while (null !== ($chunk = $this->readChunk(4096))) {
            $data .= $chunk;
        }

        return $data;
    }

    /**
     * Transfer available data to a write instance
     */
    public function readTo(IWriter $writer): void
    {
        $this->checkReadable();

        while (null !== ($chunk = $this->read(4096))) {
            $writer->write($chunk);
        }
    }

    /**
     * Check the resource is readable and throw exception if not
     */
    protected function checkReadable(): void
    {
        if (!$this->isReadable()) {
            throw Df\Error::ERuntime('Reading has been shut down');
        }
    }





    /**
     * Is the resource still writable?
     */
    public function isWritable(): bool
    {
        return true;
    }

    /**
     * Write a single line of data
     */
    public function writeLine(string $data=''): int
    {
        return $this->write($data."\r\n");
    }

    /**
     * Pluck and write $length bytes from buffer
     */
    public function writeBuffer(string &$buffer, int $length): int
    {
        $result = $this->write($buffer, $length);
        $buffer = substr($buffer, $result);
        return $result;
    }

    /**
     * Transfer data from reader
     */
    public function writeFrom(IReader $reader): void
    {
        $this->checkWritable();

        while (null !== ($chunk = $reader->read(4096))) {
            $this->write($chunk);
        }
    }

    /**
     * Check the resource is readable and throw exception if not
     */
    protected function checkWritable(): void
    {
        if (!$this->isWritable()) {
            throw Df\Error::ERuntime('Writing has been shut down');
        }
    }
}
