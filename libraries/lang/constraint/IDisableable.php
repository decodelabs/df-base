<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\lang\constraint;

use df;
use df\lang;


interface IDisableable
{
    public function isDisabled(): bool;
    public function setDisabled(bool $disabled): IDisableable;
}
