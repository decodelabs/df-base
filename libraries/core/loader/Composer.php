<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\core\loader;

use df;
use df\core;

use Composer\Autoload\ClassLoader;

class Composer implements core\ILoader
{
    public $autoload;

    public $vendorDir;
    public $baseDir;

    protected $apexPaths = [];
    protected $libraryPaths = [];

    public function __construct(ClassLoader $autoload)
    {
        $this->autoload = $autoload;

        $reflection = new \ReflectionClass(ClassLoader::class);
        $this->vendorDir = dirname(dirname($reflection->getFileName()));
        $this->baseDir = dirname($this->vendorDir);
    }

    public function loadPackages(array $packages): void
    {
        $this->apexPaths = [$this->baseDir.'/app'];
        $this->libraryPaths = [$this->baseDir.'/libraries'];

        foreach (array_reverse($packages) as $package) {
            $this->apexPaths[] = $this->vendorDir.'/decodelabs/df-'.$package;
            $this->libraryPaths[] = $this->vendorDir.'/decodelabs/df-'.$package.'/libraries';
        }

        $this->autoload->setPsr4('df\\apex\\', $this->apexPaths);
        $this->autoload->setPsr4('df\\', $this->libraryPaths);
    }

    public function getApexPaths(): array
    {
        return $this->apexPaths;
    }

    public function getLibraryPaths(): array
    {
        return $this->libraryPaths;
    }
}
