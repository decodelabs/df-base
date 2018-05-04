<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Loader;

use Df;
use Df\Core\ILoader;

use Composer\Autoload\ClassLoader;

class Composer implements ILoader
{
    const APEX = [
        'http', 'themes'
    ];

    protected $vendorPath;
    protected $basePath;

    protected $apexPaths = [];
    protected $libraryPaths = [];

    protected $packages = [];

    protected $autoload;


    /**
     * Construct with default Composer ClassLoader
     */
    public function __construct(ClassLoader $autoload)
    {
        $reflection = new \ReflectionClass(ClassLoader::class);
        $this->vendorPath = dirname(dirname($reflection->getFileName()));
        $this->basePath = dirname($this->vendorPath);

        $this->autoload = $autoload;
    }


    /**
     * Take a list of package names and install them into the loader
     */
    public function loadPackages(array $packages): void
    {
        $packages = array_reverse($packages);
        $packages[] = 'base';
        $packages = array_unique($packages);

        $this->apexPaths = [$this->basePath];
        $this->libraryPaths = [$this->basePath.'/libraries'];

        foreach ($packages as $package) {
            $this->apexPaths[] = $this->vendorPath.'/decodelabs/df-'.$package;
            $this->libraryPaths[] = $this->vendorPath.'/decodelabs/df-'.$package.'/libraries';
        }
        
        $this->packages = array_reverse($packages);
        $this->packages[] = 'app';

        foreach (static::APEX as $folder) {
            $this->autoload->setPsr4('Df\\Apex\\'.ucfirst($folder).'\\', array_map(function ($path) use ($folder) {
                return $path.'/'.$folder;
            }, $this->apexPaths));
        }

        $this->autoload->setPsr4('Df\\', $this->libraryPaths);
    }


    /**
     * List of ordered package names
     */
    public function getLoadedPackages(): array
    {
        return $this->packages;
    }


    /**
     * Get autoload handler
     */
    public function getAutoLoadHandler(): ClassLoader
    {
        return $this->autoload;
    }


    /**
     * Path to app/
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Path to app/vendor
     */
    public function getVendorPath(): string
    {
        return $this->vendorPath;
    }



    /**
     * List of package paths for app specific files
     */
    public function getApexPaths(): array
    {
        return $this->apexPaths;
    }

    /**
     * List of package paths for library class files
     */
    public function getLibraryPaths(): array
    {
        return $this->libraryPaths;
    }


    /**
     * Filter debug args
     */
    public function __debugInfo(): array
    {
        return $this->apexPaths;
    }
}