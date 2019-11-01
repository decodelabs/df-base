<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query;

use Df\Opal\Query\Source\Reference;

interface Field
{
    public function getAlias(): string;
    public function getSourceReference(): Reference;
    public function matches(Field $field): bool;
    public function __toString(): string;
}
