<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Time;

use Carbon\CarbonInterval;
use DecodeLabs\Exceptional;

class Interval extends CarbonInterval
{
    /**
     * Ensure input is either instance of Interval or null
     */
    public static function instance($duration): ?Interval
    {
        if ($duration === null) { /** @phpstan-ignore-line */
            return null;
        } elseif ($duration instanceof Interval) {
            return $duration;
        } elseif (is_int($duration)) {
            return static::instance(static::seconds($duration));
        } elseif (is_string($duration)) {
            return static::fromString($duration);
        } elseif ($duration instanceof \DateInterval) {
            return parent::instance($duration);
        } elseif (is_array($duration)) {
            return static::create(...$duration);
        } else {
            throw Exceptional::InvalidArgument(
                'Invalid duration format', null, $duration
            );
        }
    }

    /**
     * Normalize carry over points
     */
    public function normalize(): Interval
    {
        $date = new \DateTime();
        $date->add($this);

        if (null === ($output = static::instance($date->diff(new \DateTime())))) {
            throw Exceptional::UnexpectedValue(
                'Unable to create instance from date', null, $date
            );
        }

        return $output;
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
