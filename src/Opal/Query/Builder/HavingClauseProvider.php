<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Clause\Provider\Having as RootHavingClauseProvider;

interface HavingClauseProvider extends Builder, RootHavingClauseProvider
{
}
