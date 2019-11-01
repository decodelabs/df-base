<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\IBuilder;
use Df\Opal\Query\Builder\ICorrelatable;

interface ICorrelated extends IBuilder
{
    public function endCorrelation(string $alias=null): ICorrelatable;
}
