<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use DecodeLabs\Glitch;

trait ExtendableTrait
{
    /**
     *
     */
    public function extend(string $name, ...$args): Extendable
    {
        Glitch::incomplete();
    }

    /**
     *
     */
    public function extendFrom(string $fieldName, $name, ...$args): Extendable
    {
        Glitch::incomplete();
    }
}
