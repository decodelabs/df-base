<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Clause;

use Df;

interface IRepresentation
{
    public function setOr(bool $or): IRepresentation;
    public function isOr(): bool;
    public function isAnd(): bool;
}
