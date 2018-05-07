<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Lang\Constraint;

use Df;

interface IRequirable
{
    public function isRequired(): bool;
    public function setRequired(bool $required): IRequirable;
}
