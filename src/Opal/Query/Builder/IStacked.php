<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\IBuilder;

interface IStacked extends IBuilder
{
    public function asOne(string $name): IStacked;
    public function asMany(string $name, string $keyField=null): IStacked;
}
