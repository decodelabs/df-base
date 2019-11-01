<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Time;

use Carbon\Carbon;
use DecodeLabs\Glitch;

class Date extends Carbon
{
    /**
     * Normalize instance or null
     */
    public static function instance($date): ?Date
    {
        if ($date === null) {
            return null;
        } elseif ($date instanceof Date) {
            return $date;
        } elseif (is_int($date)) {
            return parent::createFromTimestamp($date);
        } elseif (is_string($date)) {
            return parent::parse($date);
        } elseif ($date instanceof \DateTime) {
            return parent::instance($date);
        } elseif (is_array($date)) {
            if (false === ($output = parent::createSafe(...$date))) {
                $output = null;
            }
            return $output;
        } else {
            throw Glitch::EInvalidArgument('Invalid date format', null, $date);
        }
    }

    /**
     * Get the difference as an Interval instance
     */
    public function diffAsInterval($date=null, $absolute=true): Interval
    {
        $output = Interval::instance($this->diff($this->resolveCarbon($date), $absolute));

        if ($output === null) {
            throw Glitch::EUnexpectedValue('Unable to create instance from date', null, $date);
        }

        return $output;
    }
}
