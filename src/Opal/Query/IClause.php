<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query;

use Df\Opal\Query\IField;
use Df\Opal\Query\Clause\IWhere;
use Df\Opal\Query\Clause\IHaving;

interface IClause extends IWhere, IHaving
{
    public function setField(IField $field): IClause;
    public function getField(): IField;

    public function setOperator(string $operator): IClause;
    public function getOperator(): string;
    public function invert(): IClause;
    public function isNegated(): bool;

    public function getPreparedValue();
}
