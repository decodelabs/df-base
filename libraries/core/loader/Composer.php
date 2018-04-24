<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\core\loader;

use df;
use df\core;

use Composer\Autoload\ClassLoader;

class Composer implements ILoader
{
    const APEX = [
        'http', 'themes'
    ];

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
        $this->apexPaths = [$this->basePath];
        $this->libraryPaths = [$this->basePath.'/libraries'];

        foreach (array_reverse($packages) as $package) {
            $this->apexPaths[] = $this->vendorPath.'/decodelabs/df-'.$package;
            $this->libraryPaths[] = $this->vendorPath.'/decodelabs/df-'.$package.'/libraries';
        }

        foreach (static::APEX as $folder) {
            $this->autoload->setPsr4('df\\apex\\'.$folder.'\\', array_map(function ($path) use ($folder) {
                return $path.'/'.$folder;
            }, $this->apexPaths));
        }

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
