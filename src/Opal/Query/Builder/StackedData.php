<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Builder\Stacked;

interface StackedData extends Stacked
{
    public function asList(string $name, $field1, $field2=null): StackedData;
    public function asValue(string $name, $field=null): StackedData;
}
