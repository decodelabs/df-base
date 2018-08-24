<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df;
use Df\Opal\Query\IBuilder;

interface IExtendable extends IBuilder
{
    public function extend(string $name, ...$args): IExtendable;
    public function extendFrom(string $fieldName, $name, ...$args): IExtendable;
}
