<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\core;

use df;
use Composer\Autoload\ClassLoader;

class Loader
{
    public $autoload;

    public $vendorDir;
    public $baseDir;

    public function __construct(ClassLoader $autoload)
    {
        $this->autoload = $autoload;

        $reflection = new \ReflectionClass(ClassLoader::class);
        $this->vendorDir = dirname(dirname($reflection->getFileName()));
        $this->baseDir = dirname($this->vendorDir);
    }

    public function loadPackages(array $packages): void
    {
        $apexPaths = [$this->baseDir.'/app'];
        $libPaths = [$this->baseDir.'/libraries'];

        foreach (array_reverse($packages) as $package) {
            $apexPaths[] = $this->vendorDir.'/decodelabs/df-'.$package;
            $libPaths[] = $this->vendorDir.'/decodelabs/df-'.$package.'/libraries';
        }

        $this->autoload->setPsr4('df\\apex\\', $apexPaths);
        $this->autoload->setPsr4('df\\', $libPaths);
    }
}
