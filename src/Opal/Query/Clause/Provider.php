<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Clause;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

interface Provider
{
    public function getSourceManager(): Manager;
    public function getPrimarySourceReference(): Reference;
    public function getPrimarySourceAlias(): string;
}
