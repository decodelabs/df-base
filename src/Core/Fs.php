<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core;

use Df;
use Df\Core\Fs\INode;
use Df\Core\Fs\File;
use Df\Core\Fs\IFile;
use Df\Core\Fs\Dir;
use Df\Core\Fs\IDir;

class Fs
{
    // Agnostic
    public static function get(string $path): INode
    {
        if (is_dir($path)) {
            return self::dir($path);
        } else {
            return self::file($path);
        }
    }

    public static function hasChanged(string $path, int $seconds=30): bool
    {
        return self::get($path)->hasChanged($seconds);
    }

    public static function setPermissions(string $path, int $permissions): INode
    {
        return self::get($path)->setPermissions($permissions);
    }

    public static function setOwner(string $path, int $owner): INode
    {
        return self::get($path)->setOwner($owner);
    }

    public static function setGroup(string $path, int $group): INode
    {
        return self::get($path)->setGroup($group);
    }

    public static function copy(string $path, string $newPath): IFile
    {
        return self::get($path)->copyTo($newPath);
    }

    public static function rename(string $path, string $newName): IFile
    {
        return self::get($path)->renameTo($newName);
    }

    public static function move(string $path, string $newPath, string $newName=null): IFile
    {
        return self::get($path)->moveTo($newPath, $newName);
    }

    public static function delete(string $path): void
    {
        self::get($path)->delete();
    }

    // File
    public static function file(string $path, string $mode=null): IFile
    {
        return new File($path, $mode);
    }

    public static function createFile(string $path, $data): IFile
    {
        return self::file($path)->putContents($data);
    }

    public static function getContents(string $path)
    {
        return self::file($path)->getContents();
    }

    public static function hasFileChanged(string $path, int $seconds=30): bool
    {
        return self::file($path)->hasChanged($seconds);
    }

    public static function setFilePermissions(string $path, int $permissions): INode
    {
        return self::file($path)->setPermissions($permissions);
    }

    public static function setFileOwner(string $path, int $owner): INode
    {
        return self::file($path)->setOwner($owner);
    }

    public static function setFileGroup(string $path, int $group): INode
    {
        return self::file($path)->setGroup($group);
    }

    public static function copyFile(string $path, string $newPath): IFile
    {
        return self::file($path)->copyTo($newPath);
    }

    public static function renameFile(string $path, string $newName): IFile
    {
        return self::file($path)->renameTo($newName);
    }

    public static function moveFile(string $path, string $newPath, string $newName=null): IFile
    {
        return self::file($path)->moveTo($newPath, $newName);
    }

    public static function deleteFile(string $path): void
    {
        self::file($path)->delete();
    }

    // Dir
    public static function dir(string $path): IDir
    {
        return new Dir($path);
    }

    public static function createDir(string $path, int $permissions=null): IDir
    {
        return self::dir($path)->ensureExists($permissions);
    }

    public static function hasDirChanged(string $path, int $seconds=30): bool
    {
        return self::dir($path)->hasChanged($seconds);
    }

    public static function setDirPermissions(string $path, int $permissions, bool $recursive=false): INode
    {
        return self::dir($path)->setPermissions($permissions, $recursive);
    }

    public static function setDirOwner(string $path, int $owner, bool $recursive): INode
    {
        return self::dir($path)->setOwner($owner, $recursive);
    }

    public static function setDirGroup(string $path, int $group, bool $recursive): INode
    {
        return self::dir($path)->setGroup($group, $recursive);
    }

    public static function copyDir(string $path, string $newPath): IDir
    {
        return self::dir($path)->copyTo($newPath);
    }

    public static function renameDir(string $path, string $newName): IDir
    {
        return self::dir($path)->renameTo($newName);
    }

    public static function moveDir(string $path, string $newPath, string $newName=null): IDir
    {
        return self::dir($path)->moveTo($newPath, $newName);
    }

    public static function deleteDir(string $path): void
    {
        self::dir($path)->delete();
    }

    public static function emptyOut(string $path): IDir
    {
        return self::dir($path)->emptyOut();
    }

    public static function merge(string $path, string $destination): IDir
    {
        return self::dir($path)->mergeInto($destination);
    }
}
