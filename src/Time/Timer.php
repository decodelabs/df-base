<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Time;

class Timer
{
    protected $start = null;
    protected $time = 0;

    /**
     * Init with optional start time
     */
    public function __construct(float $start=null)
    {
        $this->start($start);
    }

    /**
     * Start counting time
     */
    public function start(float $start=null): Timer
    {
        $this->start = $start ?? microtime(true);
        return $this;
    }

    /**
     * Count currently elapsed time
     */
    public function stop(): Timer
    {
        if ($this->start !== null) {
            $this->time += microtime(true) - $this->start;
            $this->start = null;
        }

        return $this;
    }

    /**
     * Get accumulated time
     */
    public function getTime(): float
    {
        $output = $this->time;

        if ($this->start !== null) {
            $output += microtime(true) - $this->start;
        }

        return $output;
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        $seconds = $this->getTime();

        if ($seconds > 60) {
            return number_format($seconds / 60, 0).':'.number_format($seconds % 60);
        } elseif ($seconds > 1) {
            return number_format($seconds, 3).' s';
        } elseif ($seconds > 0.0005) {
            return number_format($seconds * 1000, 2).' ms';
        } else {
            return number_format($seconds * 1000, 5).' ms';
        }
    }

    /**
     * Show debug info
     */
    public function __debugInfo(): array
    {
        return [
            'time' => $this->__toString()
        ];
    }
}
