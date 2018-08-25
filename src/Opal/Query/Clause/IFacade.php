<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Clause;

use Df;
use Df\Opal\Query\IBuilder;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

interface IFacade
{
    public function getSourceManager(): Manager;
    public function getPrimarySourceReference(): Reference;
    public function getPrimarySourceAlias(): string;
}
