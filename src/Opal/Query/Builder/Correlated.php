<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Builder\Correlatable;

interface Correlated extends Builder
{
    public function endCorrelation(string $alias=null): Correlatable;
}
