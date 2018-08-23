<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query;

use Df;
use Df\Opal\Query\Source\Reference;

interface IField
{
    public function getAlias(): string;
    public function getSourceReference(): Reference;
    public function matches(IField $field): bool;
    public function __toString(): string;
}
