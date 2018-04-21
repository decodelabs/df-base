<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data;

use df;
use df\data;

class Sequence implements \IteratorAggregate, ISequence
{
    use namespace\arrayCollection\TMutableSequence;



    /**
     * Create a new mutable copy
     */
    public function copyMutable(): ICollection
    {
        return clone $this;
    }

    /**
     * Create an immutable copy
     */
    public function copyImmutable(): ICollection
    {
        df\incomplete();
    }
}
