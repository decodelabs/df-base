<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data;

use df;

class HashMap implements \IteratorAggregate, IHashMap
{
    use namespace\arrayCollection\THashMap;

    const MUTABLE = false;
}
