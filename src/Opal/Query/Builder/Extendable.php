<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;

interface Extendable extends Builder
{
    public function extend(string $name, ...$args): Extendable;
    public function extendFrom(string $fieldName, $name, ...$args): Extendable;
}
