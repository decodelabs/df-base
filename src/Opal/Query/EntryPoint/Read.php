<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\EntryPoint;

use Df\Opal\Query\Initiator\Select as SelectInitiator;
use Df\Opal\Query\Initiator\Fetch as FetchInitiator;
use Df\Opal\Query\Initiator\Union as UnionInitiator;

interface Read
{
    public function select(string ...$fields): SelectInitiator;
    public function selectDistinct(string ...$fields): SelectInitiator;
    //public function union(string ...$fields): UnionInitiator;
    //public function fetch(): FetchInitiator;
}
