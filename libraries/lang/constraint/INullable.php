<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\lang\constraint;

use df;

interface INullable
{
    public function isNullable(): bool;
    public function setNullable(bool $nullable): INullable;
}
