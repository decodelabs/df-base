<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Lang\Constraint;

use Df;

interface IReadOnly
{
    public function isReadOnly(): bool;
    public function setReadOnly(bool $readOnly): IReadOnly;
}
