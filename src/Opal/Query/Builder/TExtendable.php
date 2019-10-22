<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use DecodeLabs\Glitch;

trait TExtendable
{
    /**
     *
     */
    public function extend(string $name, ...$args): IExtendable
    {
        Glitch::incomplete();
    }

    /**
     *
     */
    public function extendFrom(string $fieldName, $name, ...$args): IExtendable
    {
        Glitch::incomplete();
    }
}
