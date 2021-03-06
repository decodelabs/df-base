<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Loader;

use Df\Core\App;
use Df\Core\Loader;

use Composer\Autoload\ClassLoader;

class Composer implements Loader
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
    public function __construct(App $app, ClassLoader $autoload)
    {
        $reflection = new \ReflectionClass(ClassLoader::class);
        $this->vendorPath = dirname(dirname((string)$reflection->getFileName()));
        $this->basePath = dirname($this->vendorPath);

        $this->autoload = $autoload;

        $class = get_class($app);
        $this->appLibraries = $class::LOAD_APP_LIBRARIES;
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
     * List of package paths for library class files
     */
    public function getBundlePaths(): array
    {
        return $this->bundlePaths;
    }


    /**
     * Filter debug args
     */
    public function __debugInfo(): array
    {
        return $this->bundlePaths;
    }
}
