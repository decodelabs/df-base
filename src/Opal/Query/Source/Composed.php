<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Source;

use Df\Opal\Query\Source;

interface Composed extends Source
{
    public function getFieldNames(): array;
}
