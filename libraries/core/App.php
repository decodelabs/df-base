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

    const SERVICES = [

    ];

    /**
     * Setup initial state
     */
    public function bootstrap(): void
    {
        // Prepare container
        $this->registerPlatformServices();

        // Loader
        $this['core.loader']->loadPackages($this::PACKAGES);

        // Errors
        error\Handler::register($this[error\IHandler::class]);
    }


    /**
     * Register all the other bindings
     */
    public function registerPlatformServices(): void
    {
        $this->registerLoaderServices();
        $this->registerErrorServices();
        $this->registerDumperServices();
        $this->registerEnvServices();
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
     * Register error handler services
     */
    protected function registerErrorServices(): void
    {
        $this->bindShared(core\error\IHandler::class, core\error\Handler::class);
        $this->bind(error\IReporter::class, error\reporter\Whoops::class);
    }

    /**
     * Register dumper services
     */
    protected function registerDumperServices(): void
    {
        //debug\dumper\Handler::register();
    }

    /**
     * Register env services
     */
    protected function registerEnvServices(): void
    {
        $this->bindShared(core\env\IConfig::class, function ($app) {
            return core\env\config\DotIni::loadFile(df\BASE_PATH.'/.env');
        });
    }
}
