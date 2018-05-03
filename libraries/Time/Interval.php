<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\time;

use df;

use Carbon\CarbonInterval;

class Interval extends CarbonInterval
{
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
