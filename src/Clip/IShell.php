<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Clip;

use Df;

use Psr\Log\LoggerInterface;

interface IShell extends LoggerInterface
{
    public function getWidth(): int;
    public function canColor(): bool;

    public function render($output, ?string $modifier=null): void;
    public function style(string $message, string $fgColor, string $bgColor=null, string ...$options): string;

    public function write(string $message): void;
    public function writeLine(?string $message=null): void;

    public function writeError(string $message): void;
    public function writeErrorLine(?string $message=null): void;

    public function read(int $size): ?string;
    public function readLine(): ?string;
}
