<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\data;

use df;
use df\data;

interface ICollection extends \Iterable, IArrayProvider
{
    public function isEmpty(): bool;

    public function isMutable(): ICollection;

    public function ensureMutable(): ICollection;
    public function ensureImmutable(): ICollection;

    public function copy(): ICollection;
    public function copyMutable(): ICollection;
    public function copyImmutable(): ICollection;
}
