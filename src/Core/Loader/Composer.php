<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Loader;

use Df;
use Df\Core\IApp;
use Df\Core\ILoader;

use Composer\Autoload\ClassLoader;

class Composer implements ILoader
{
    protected $appLibraries = false;

    protected $vendorPath;
    protected $basePath;

    protected $bundles = [];
    protected $bundlePaths = [];

    protected $autoload;


    /**
     * Construct with default Composer ClassLoader
     */
    public function __construct(IApp $app, ClassLoader $autoload)
    {
        $reflection = new \ReflectionClass(ClassLoader::class);
        $this->vendorPath = dirname(dirname($reflection->getFileName()));
        $this->basePath = dirname($this->vendorPath);

        $this->autoload = $autoload;
        $this->appLibraries = $app::LOAD_APP_LIBRARIES;
    }


    /**
     * Take a list of package names and install them into the loader
     */
    public function loadBundles(array $bundles): void
    {
        // Sort bundle list
        $this->bundles = Bundle::getRegistered();

        uasort($this->bundles, function ($a, $b) {
            return $b->getPriority() <=> $a->getPriority();
        });


        // Extract bundle paths
        foreach ($this->bundles as $name => $bundle) {
            foreach ($bundle->getPaths() as $pathName => $path) {
                // Add to loader
                $parts = array_map('ucfirst', explode('.', $pathName));
                $namespace = 'Df\\'.implode('\\', $parts).'\\';
                $this->autoload->addPsr4($namespace, [$path]);

                // Add to path list
                $this->bundlePaths[implode('/', $parts)][] = $path;
            }
        }
    }


    /**
     * List of ordered package names
     */
    public function getLoadedBundles(): array
    {
        return $this->bundles;
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
