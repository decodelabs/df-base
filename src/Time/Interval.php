<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Time;

use Df;

use Carbon\CarbonInterval;

class Interval extends CarbonInterval
{
    /**
     * Ensure input is either instance of Interval or null
     */
    public static function normalizeInstance($duration): ?Interval
    {
        if ($duration === null) {
        } elseif (is_int($duration)) {
            return static::seconds($duration);
        } elseif (is_string($duration)) {
            return static::fromString($duration);
        } elseif ($duration instanceof \DateInterval) {
            return static::instance($duration);
        } elseif (is_array($duration)) {
            return static::create(...$duration);
        } else {
            throw Df\Error::EInvalidArgument('Invalid duration format', null, $duration);
        }
    }

    /**
     * Normalize carry over points
     */
    public function normalize(): Interval
    {
        $date = new \DateTime();
        $date->add($this);
        return static::instance($date->diff(new \DateTime()));
    }

    /**
     * Dump info
     */
    public function __debugInfo(): array
    {
        return [
            'human' => $this->forHumans()
        ];
    }
}
