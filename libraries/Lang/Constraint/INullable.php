<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Lang\Constraint;

use Df;

interface INullable
{
    public function isNullable(): bool;
    public function setNullable(bool $nullable): INullable;
}
