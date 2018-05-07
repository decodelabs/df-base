<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Data;

use Df;

class Sequence implements \IteratorAggregate, ISequence
{
    use namespace\ArrayCollection\TSequence;

    const MUTABLE = false;
}
