<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Clause;

interface Representation
{
    public function setOr(bool $or): Representation;
    public function isOr(): bool;
    public function isAnd(): bool;
}
