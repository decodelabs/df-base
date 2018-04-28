<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\lang\debug;

use df;
use df\lang;

class StackTrace implements \IteratorAggregate
{
    protected $frames= [];

    /**
     * Extract trace from exception and build
     */
    public static function createFromException(\Throwable $e, int $rewind=0): StackTrace
    {
        return self::createFromBacktrace($e->getTrace(), $rewind);
    }

    /**
     * Generate a backtrace and build
     */
    public static function create(int $rewind=0): StackTrace
    {
        return self::createFromBacktrace(debug_backtrace(), $rewind + 1);
    }

    /**
     * Take a trace array and convert to objects
     */
    public static function createFromBacktrace(array $trace, int $rewind=0): StackTrace
    {
        $last = null;

        if ($rewind) {
            if ($rewind > count($trace) - 1) {
                throw df\Error::EOutOfRange('Stack rewind out of stack frame range', [
                    'data' => [
                        'rewind' => $rewind,
                        'trace' => $trace
                    ]
                ]);
            }

            while ($rewind >= 0) {
                $rewind--;
                $last = array_shift($trace);
            }
        }

        if (!$last) {
            $last = array_shift($trace);
        }

        $last['fromFile'] = $last['file'] ?? null;
        $last['fromLine'] = $last['line'] ?? null;
        $output = [];

        foreach ($trace as $frame) {
            $frame['fromFile'] = $frame['file'] ?? null;
            $frame['fromLine'] = $frame['line'] ?? null;
            $frame['file'] = $last['fromFile'];
            $frame['line'] = $last['fromLine'];

            $output[] = new StackFrame($frame);
            $last = $frame;
        }

        return new self($output);
    }


    /**
     * Check list of frames
     */
    public function __construct(array $frames)
    {
        foreach ($frames as $frame) {
            if (!$frame instanceof StackFrame) {
                throw df\Error::EUnexpectedValue([
                    'message' => 'Trace frame is not an instance of df\\lang\\StackFrame',
                    'data' => $frame
                ]);
            }

            $this->frames[] = $frame;
        }
    }



    /**
     * Get the frame list as an array
     */
    public function getFrames(): array
    {
        return $this->frames;
    }

    /**
     * Get first frame
     */
    public function getFirstFrame(): StackFrame
    {
        return $this->frames[0];
    }


    /**
     * Get file from first frame
     */
    public function getFile(): ?string
    {
        return $this->frames[0]->getFile();
    }

    /**
     * Get line from first frame
     */
    public function getLine(): ?int
    {
        return $this->frames[0]->getLine();
    }



    /**
     * Create iterator
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->frames);
    }


    /**
     * Export to generic array
     */
    public function toArray(): array
    {
        return array_map(function ($frame) {
            return $frame->toArray();
        }, $this->frames);
    }



    /**
     * Debug info
     */
    public function __debugInfo(): array
    {
        $output = [];
        $frames = $this->getFrames();
        $count = count($frames);

        foreach ($frames as $i => $frame) {
            if ($i === 0) {
                $output[($count + 1).': df\\Error()'] =
                    df\stripBasePath($frame->getFile()).' : '.$frame->getLine();
            }

            $output[($count - $i).': '.$frame->getSignature(true)] =
                df\stripBasePath($frame->getCallingFile()).' : '.$frame->getCallingLine();
        }

        return $output;
    }
}
