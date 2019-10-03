<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Fs;

use DecodeLabs\Atlas\Channel;

interface IFile extends INode, Channel
{
    public function getSize(): ?int;
    public function getHash(string $type, bool $raw=false): ?string;

    public function putContents($data): IFile;
    public function getContents(): ?string;

    public function open(string $mode): IFile;
    public function isOpen(): bool;
    public function isLink(): bool;
    public function getIoMode(): ?string;

    public function setPermissions(int $mode): IFile;
    public function setOwner(int $owner): IFile;
    public function setGroup(int $group): IFile;

    public function lock(bool $nonBlocking=false): bool;
    public function lockExclusive(bool $nonBlocking=false): bool;
    public function unlock(): IFile;

    public function seek(int $offet, int $whence=SEEK_SET): IFile;
    public function tell(): int;
    public function flush(): IFile;
    public function truncate(int $size=0): IFile;
}
