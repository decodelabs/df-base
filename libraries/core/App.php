<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\core;

use df;
use df\core;
use df\core\service;
use df\core\error;
use df\lang;

use Composer\Autoload\ClassLoader;

class App extends service\Container implements IApp
{
    const PACKAGES = [];
    const PROVIDERS = [];

    const DEFAULT_PROVIDERS = [
        env\EnvServiceProvider::class,
        error\ErrorServiceProvider::class
    ];

    /**
     * Setup initial state
     */
    public function bootstrap(): void
    {
        /* Set basic PHP defaults */
        $this->setPlatformDefaults();

        /* The loader needs to be set up manually before
         * everything else to ensure custom classes are found */
        $this->registerLoaderServices();
        $this['core.loader']->loadPackages($this::PACKAGES);


        /* Load up all the available providers */
        $this->registerProviders(...$this::DEFAULT_PROVIDERS);
        $this->registerProviders(...$this::PROVIDERS);

        /* Register error handler */
        error\Handler::register($this['core.error.handler']);
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
        $loader = require df\BASE_PATH.'/vendor/autoload.php';
        $this->bindShared(ClassLoader::class, $loader);

        /* Register the main loader handler */
        $this->bindShared(core\ILoader::class, core\loader\Composer::class);
    }


    /**
     * Get root app path
     */
    public function getBasePath(): string
    {
        return df\BASE_PATH;
    }

    /**
     * Get composer vendor lib path
     */
    public function getVendorPath(): string
    {
        return df\BASE_PATH.'/vendor';
    }

    /**
     * Get public entry point path
     */
    public function getPublicPath(): string
    {
        return df\BASE_PATH.'/public';
    }
}
