<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Fs;

use Df;

interface IDir extends INode
{
    public function ensureExists(int $permissions=null): IDir;
    public function isEmpty(): bool;

    public function setPermissions(int $mode, bool $recursive=false): IDir;
    public function setOwner(int $owner, bool $recursive=false): IDir;
    public function setGroup(int $group, bool $recursive=false): IDir;

    public function scan(callable $filter=null): \Generator;
    public function scanNames(callable $filter=null): \Generator;
    public function countContents(callable $filter=null): int;

    public function scanFiles(callable $filter=null): \Generator;
    public function scanFileNames(callable $filter=null): \Generator;
    public function countFiles(callable $filter=null): int;

    public function scanDirs(callable $filter=null): \Generator;
    public function scanDirNames(callable $filter=null): \Generator;
    public function countDirs(callable $filter=null): int;

    public function scanRecursive(callable $filter=null): \Generator;
    public function scanNamesRecursive(callable $filter=null): \Generator;
    public function countContentsRecursive(callable $filter=null): int;

    public function scanFilesRecursive(callable $filter=null): \Generator;
    public function scanFileNamesRecursive(callable $filter=null): \Generator;
    public function countFilesRecursive(callable $filter=null): int;

    public function scanDirsRecursive(callable $filter=null): \Generator;
    public function scanDirNamesRecursive(callable $filter=null): \Generator;
    public function countDirsRecursive(callable $filter=null): int;

    public function getParent(): ?IDir;
    public function getChild(string $name): ?INode;
    public function hasChild(string $name): bool;
    public function deleteChild(string $name): INode;

    public function createDir(string $name, int $permissions=null): IDir;
    public function hasDir(string $name): bool;
    public function getDir(string $name, bool $ifExists=false): ?IDir;
    public function deleteDir(string $name): IDir;

    public function createFile(string $name, string $content): IFile;
    public function openFile(string $name, string $mode): IFile;
    public function hasFile(string $name): bool;
    public function getFile(string $name, bool $ifExists=false): ?IFile;
    public function deleteFile(string $name): IDir;

    public function emptyOut(): IDir;
    public function mergeInto(string $destination): IDir;
}
