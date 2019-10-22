<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Clip;

interface IShell
{
    public function getWidth(): int;
    public function canColor(): bool;

    public function write(string $message): void;
    public function writeLine(?string $message=null): void;

    public function writeError(string $message): void;
    public function writeErrorLine(?string $message=null): void;

    public function read(int $size): ?string;
    public function readLine(): ?string;
}
