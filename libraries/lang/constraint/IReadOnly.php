<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\lang\constraint;

use df;
use df\lang;

interface IReadOnly
{
    public function isReadOnly(): bool;
    public function setReadOnly(bool $readOnly): IReadOnly;
}