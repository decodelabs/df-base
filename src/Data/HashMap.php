<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Data;

use Df;

class HashMap implements \IteratorAggregate, IHashMap
{
    use namespace\ArrayCollection\THashMap;

    const MUTABLE = false;
}
