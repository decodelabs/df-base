<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\IBuilder;
use Df\Opal\Query\Clause\IHaving;
use Df\Opal\Query\Clause\IHavingFacade;

interface IHavingClauseProvider extends IBuilder, IHavingFacade
{
}
