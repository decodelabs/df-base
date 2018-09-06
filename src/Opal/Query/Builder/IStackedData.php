<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df;
use Df\Opal\Query\IBuilder;

interface IStackedData extends IStacked
{
    public function asList(string $name, $field1, $field2=null): IStackedData;
    public function asValue(string $name, $field=null): IStackedData;
}
