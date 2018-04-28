<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\core;

use df;
use df\core;
use df\core\container;
use df\core\error;
use df\lang\debug;

use Composer\Autoload\ClassLoader;

class App extends container\Generic implements IApp
{
    const PACKAGES = [];
    const PROVIDERS = [];

    const DEFAULT_PROVIDERS = [
        core\env\EnvServiceProvider::class,
        core\error\ErrorServiceProvider::class
    ];

    /**
     * Setup initial state
     */
    public function bootstrap(): void
    {
        /* The loader needs to be set up manually before
         * everything else to ensure custom classes are found */
        $this->registerLoaderServices();
        $this['core.loader']->loadPackages($this::PACKAGES);


        /* Load up all the available providers */
        $this->registerProviders(...$this::DEFAULT_PROVIDERS);
        $this->registerProviders(...$this::PROVIDERS);

        /* Register error handler */
        error\Handler::register($this['core.error.handler']);
        debug\dumper\Handler::register();
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
}
