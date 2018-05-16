<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Fs;

use Df;

interface INode
{
    public function getPath(): string;
    public function getName(): string;
    public function exists(): bool;
    public function clearStatCache(): INode;
    public function getLastModified(): ?int;
    public function hasChanged(int $timeout=30): bool;

    public function getPermissions(): ?int;
    public function getOwner(): ?int;
    public function getGroup(): ?int;

    public function copyTo(string $destination): INode;
    public function renameTo(string $newName): INode;
    public function moveTo(string $destination, string $newName=null): INode;
    public function delete(): void;
}
