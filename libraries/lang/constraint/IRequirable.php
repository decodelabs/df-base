<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\lang\constraint;

use df;
use df\lang;


interface IRequirable
{
    public function isRequired(): bool;
    public function setRequired(bool $required): IRequirable;
}
