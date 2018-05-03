<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data;

use df;

class Sequence implements \IteratorAggregate, ISequence
{
    use namespace\arrayCollection\TSequence;

    const MUTABLE = false;
}
