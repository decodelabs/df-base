<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\time;

use df;
use df\time;

use Carbon\Carbon;

class Date extends Carbon
{
    /**
     * Get the difference as an Interval instance
     */
    public function diffAsInterval($date = null, $absolute = true): Interval
    {
        return Interval::instance($this->diff($this->resolveCarbon($date), $absolute));
    }
}
