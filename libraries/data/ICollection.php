<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\data;

use df;
use df\data;

interface ICollection extends \Traversable, IArrayProvider, \JsonSerializable
{
    public function isEmpty(): bool;

    public function isMutable(): bool;

    public function copy(): ICollection;
}
