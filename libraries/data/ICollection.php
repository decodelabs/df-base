<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\data;

use df;
use df\data;

interface ICollection extends \Traversable, \ArrayAccess, IArrayProvider
{
    public function isEmpty(): bool;

    public function isMutable(): bool;

    public function ensureMutable(): ICollection;
    public function ensureImmutable(): ICollection;

    public function copy(): ICollection;
    public function copyMutable(): ICollection;
    public function copyImmutable(): ICollection;
}
