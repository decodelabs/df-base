<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\lang\debug;

use df;
use df\lang;

class StackTrace implements \IteratorAggregate
{
    protected $calls= [];

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

        if($rewind) {
            if ($rewind > count($trace) - 1) {
                throw df\Error::EOutOfRange('Stack rewind out of stack call range', [
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

        foreach ($trace as $call) {
            $call['fromFile'] = $call['file'] ?? null;
            $call['fromLine'] = $call['line'] ?? null;
            $call['file'] = $last['fromFile'];
            $call['line'] = $last['fromLine'];

            $output[] = new StackCall($call);
            $last = $call;
        }

        return new self($output);
    }


    /**
     * Check list of calls
     */
    public function __construct(array $calls)
    {
        foreach ($calls as $call) {
            if (!$call instanceof StackCall) {
                throw df\Error::EUnexpectedValue([
                    'message' => 'Trace call is not an instance of df\\lang\\StackCall',
                    'data' => $call
                ]);
            }

            $this->calls[] = $call;
        }

        if(empty($this->calls)) {
            throw df\Error::EUnderflow('Stack trace is empty');
        }
    }



    /**
     * Get the call list as an array
     */
    public function getCalls(): array
    {
        return $this->calls;
    }

    /**
     * Get first call
     */
    public function getFirstCall(): StackCall
    {
        return $this->calls[0];
    }


    /**
     * Get file from first call
     */
    public function getFile(): ?string
    {
        return $this->calls[0]->getFile();
    }

    /**
     * Get line from first call
     */
    public function getLine(): ?int
    {
        return $this->calls[0]->getLine();
    }



    /**
     * Create iterator
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->calls);
    }


    /**
     * Export to generic array
     */
    public function toArray(): array
    {
        return array_map(function($call) {
            return $call->toArray();
        }, $this->calls);
    }
}
