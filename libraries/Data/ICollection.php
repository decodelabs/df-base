<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Data;

use Df;

interface ICollection extends \Traversable, IArrayProvider, \JsonSerializable
{
    public function isEmpty(): bool;

    public function isMutable(): bool;

    public function copy(): ICollection;
}
