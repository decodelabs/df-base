<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Mutex;

use Df;
use Df\Core\Fs\File as Fs;

class File implements ILock
{
    use TLock {
        TLock::__construct as private __lockConstruct;
    }

    protected $file;

    /**
     * Init with name and path
     */
    public function __construct(string $name, string $path)
    {
        $this->__lockConstruct($name);
        $this->file = new Fs($path.'/'.$name.'.lock');
    }


    /**
     * Create file and lock it
     */
    protected function acquireLock(bool $blocking): bool
    {
        if ($this->file->exists()) {
            return false;
        }

        $this->file->open('c');
        return $this->file->lockExclusive(true);
    }

    /**
     * Release file and delete it
     */
    protected function releaseLock(): void
    {
        $this->file->unlock()->close();
        $this->file->delete();
    }
}
