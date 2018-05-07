<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Lang\Constraint;

use Df;

interface IDisableable
{
    public function isDisabled(): bool;
    public function setDisabled(bool $disabled): IDisableable;
}
