<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query;

use Df\Opal\Query\Field;
use Df\Opal\Query\Clause\Representation\Where as WhereRepresentation;
use Df\Opal\Query\Clause\Representation\Having as HavingRepresentation;

interface Clause extends WhereRepresentation, HavingRepresentation
{
    public function setField(Field $field): Clause;
    public function getField(): Field;

    public function setOperator(string $operator): Clause;
    public function getOperator(): string;
    public function invert(): Clause;
    public function isNegated(): bool;

    public function getPreparedValue();
}
