<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Mutex;

use Df;

interface ILock
{
    public function getName(): string;

    public function lock(int $timeout=null): bool;
    public function unlock(): void;

    public function isLocked(): bool;
    public function countLocks(): int;
}
