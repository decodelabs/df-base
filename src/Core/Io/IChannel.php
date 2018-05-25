<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Io;

use Df;

interface IChannel
{
    public function setBlocking(bool $flag): void;
    public function isBlocking(): bool;

    public function isReadable(): bool;
    public function read(int $length): ?string;
    public function readAll(): ?string;
    public function readLine(): ?string;
    public function writeTo(IChannel $writer): void;

    public function isWritable(): bool;
    public function write(?string $data, int $length=null): int;
    public function writeLine(?string $data=''): int;
    public function writeBuffer(string &$buffer, int $length): int;

    public function eof(): bool;
    public function close(): void;
    public function getResource();
}
