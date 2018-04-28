<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\core\error;

use df;
use df\core;

interface IHandler
{
    public function handleError(int $number, string $message, string $file, int $line): bool;
    public function handleException(\Throwable $exception): void;
    public function handleShutdown(): void;
}
