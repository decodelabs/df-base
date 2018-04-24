<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data;

use df;
use df\data;

class HashMap implements \IteratorAggregate, IHashMap
{
    use namespace\arrayCollection\THashMap;

    const MUTABLE = false;
}
