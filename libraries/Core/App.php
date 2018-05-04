<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core;

use Df;

use Df\Core\Service\Container;
use Df\Core\Config\ServiceProvider as ConfigServiceProvider;
use Df\Core\Env\ServiceProvider as EnvServiceProvider;
use Df\Core\Error\ServiceProvider as ErrorServiceProvider;
use Df\Core\Error\Handler as ErrorHandler;

use Df\Core\ILoader;
use Df\Core\Loader\Composer as ComposerLoader;

use Df\Core\Kernel\IHttp as IHttpKernel;
use Df\Core\Kernel\IConsole as IConsoleKernel;

use Df\Clip\Kernel as ConsoleKernel;

use Df\Http\Kernel as HttpKernel;
use Df\Http\ServiceProvider as HttpServiceProvider;
use Df\Http\Middleware;

use Df\Arch\ServiceProvider as ArchServiceProvider;
use Df\Arch\Pipeline\IHandler as ArchHandler;

use Composer\Autoload\ClassLoader;

class App extends Container implements IApp
{
    const PACKAGES = [];
    const PROVIDERS = [];

    const DEFAULT_PROVIDERS = [
        ConfigServiceProvider::class,
        ErrorServiceProvider::class,
        HttpServiceProvider::class,
        ArchServiceProvider::class
    ];

    const MIDDLEWARE = [];

    const DEFAULT_MIDDLEWARE = [
        //Middleware\GlobalRequests::class => -99,
        // self::MIDDLEWARE
        ArchHandler::class => 99
    ];


    /**
     * Setup initial state
     */
    public function bootstrap(): void
    {
        /* Register self */
        $this->bindShared(IApp::class, $this);

        /* Set basic PHP defaults */
        $this->setPlatformDefaults();

        /* The loader needs to be set up manually before
         * everything else to ensure custom classes are found */
        $this->registerLoaderServices();
        $this[ILoader::class]->loadPackages($this::PACKAGES);

        /* Load up all the available providers */
        $this->registerProviders(...$this::DEFAULT_PROVIDERS);
        $this->registerProviders(...$this::PROVIDERS);

        /* Load kernels */
        $this->registerHttpKernel();
        $this->registerConsoleKernel();
    }


    /**
     * Setup basic PHP platform defaults
     */
    protected function setPlatformDefaults(): void
    {
        error_reporting(-1);
        date_default_timezone_set('UTC');
        mb_internal_encoding('UTF-8');
    }


    /**
     * Register loader services
     */
    protected function registerLoaderServices(): void
    {
        /* Add the generic composer autoloader to the container
         * before doing anything else so stuff can be loaded */
        $loader = require Df\BASE_PATH.'/vendor/autoload.php';
        $this->bindShared(ClassLoader::class, $loader);

        /* Register the main loader handler */
        $this->bindShared(ILoader::class, ComposerLoader::class);
    }

    /**
     * Register HTTP kernel
     */
    protected function registerHttpKernel(): void
    {
        $this->bindShared(IHttpKernel::class, HttpKernel::class);
    }

    /**
     * Register console kernel
     */
    protected function registerConsoleKernel(): void
    {
        $this->bindShared(IConsoleKernel::class, ConsoleKernel::class);
    }



    /**
     * Combine middleware array for global dispatcher
     */
    public function getGlobalMiddleware(): array
    {
        $middleware = array_merge(static::DEFAULT_MIDDLEWARE, static::MIDDLEWARE);
        $output = [];
        $i = 0;

        foreach ($middleware as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_string($key)) {
                $type = $key;
                $priority = (int)$value;
            } else {
                $type = $value;
                $priority = $i++;
            }

            while (isset($output[$priority])) {
                $priority++;
            }

            $output[$priority] = $type;
        }

        ksort($output);
        return array_values($output);
    }



    /**
     * Get root app path
     */
    public function getBasePath(): string
    {
        return Df\BASE_PATH;
    }

    /**
     * Get composer vendor lib path
     */
    public function getVendorPath(): string
    {
        return Df\BASE_PATH.'/vendor';
    }

    /**
     * Get public entry point path
     */
    public function getPublicPath(): string
    {
        return Df\BASE_PATH.'/public';
    }


    /**
     * Tidy things up before exiting
     */
    public function terminate(): void
    {
        // nothing to do yet
    }
}