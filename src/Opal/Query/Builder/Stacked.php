<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;

interface Stacked extends Builder
{
    public function asOne(string $name): Stacked;
    public function asMany(string $name, string $keyField=null): Stacked;
}
