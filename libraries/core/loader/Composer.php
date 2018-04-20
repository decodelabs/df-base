<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\core\loader;

use df;
use df\core;

use Composer\Autoload\ClassLoader;

class Composer implements ILoader
{
    public $autoload;

    public $vendorPath;
    public $basePath;

    protected $apexPaths = [];
    protected $libraryPaths = [];

    public function __construct(ClassLoader $autoload)
    {
        $this->autoload = $autoload;

        $reflection = new \ReflectionClass(ClassLoader::class);
        $this->vendorPath = dirname(dirname($reflection->getFileName()));
        $this->basePath = dirname($this->vendorPath);
    }

    public function loadPackages(array $packages): void
    {
        $this->apexPaths = [$this->basePath.'/app'];
        $this->libraryPaths = [$this->basePath.'/libraries'];

        foreach (array_reverse($packages) as $package) {
            $this->apexPaths[] = $this->vendorPath.'/decodelabs/df-'.$package;
            $this->libraryPaths[] = $this->vendorPath.'/decodelabs/df-'.$package.'/libraries';
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
