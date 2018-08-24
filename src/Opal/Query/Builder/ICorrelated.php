<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df;
use Df\Opal\Query\IBuilder;

interface ICorrelated extends IBuilder
{
    public function endCorrelation(string $alias=null): ICorrelated;
}
