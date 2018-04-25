<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);


/**
 * global helpers
 */
namespace
{
    use df\lang\debug;

    if (!function_exists('dd')) {
        /**
         * Super quick global dump
         */
        function dd(...$vars): void
        {
            debug\dumper\Handler::dump(...$vars);
        }
    }
}


/**
 * df helper
 */
namespace df
{

    use df;
    use df\core;
    use df\lang\error;
    use df\lang\debug;

    use Composer\Autoload\ClassLoader;
    use Whoops;
    use Whoops\Handler\HandlerInterface as WhoopsHandler;

    define('df\\START', microtime(true));


    /**
     * Initial bootstrap
     */
    function bootstrap(): core\IContainer
    {
        static $started;

        if ($started) {
            throw new \RuntimeException('df has already been bootstrapped');
        }

        $started = true;
        $class = new \ReflectionClass(ClassLoader::class);
        [$basePath,] = explode('/vendor/', $class->getFileName(), 2);

        if (file_exists($basePath.'/App.php')) {
            require_once $basePath.'/App.php';
        }

        $loader = require $basePath.'/vendor/autoload.php';
        debug\dumper\Handler::register();

        $app = df\app();


        // Autoload
        $app->bindShared(ClassLoader::class, $loader)
            ->alias('core.autoload');


        // Whoops
        $app->bindShared(Whoops\Run::class)
            ->alias('lang.whoops')
            ->afterResolving(function ($whoops, $app) {
                $app->bindOnce(WhoopsHandler::class, Whoops\Handler\PrettyPageHandler::class);

                $app->each(WhoopsHandler::class, function ($handler) use ($whoops) {
                    $whoops->pushHandler($handler);
                });

                $whoops->register();
            })
            ->getInstance();


        // Loader
        $app->bindShared(core\ILoader::class, core\loader\Composer::class)
            ->alias('core.loader')
            ->afterResolving(function ($loader, $app) {
                $loader->loadPackages($app::PACKAGES);
            })
            ->getInstance();


        // Env
        $app
            ->bindShared(core\env\IConfig::class, function ($app) {
                return core\env\DotIni::loadFile($app['loader']->getBasePath().'/.env');
            })
            ->alias('core.env');

        return $app;
    }


    /**
     * Get active app instance
     */
    function app(): core\IApp
    {
        static $app;

        if (!isset($app)) {
            if (class_exists('df\apex\App', true)) {
                $app = new df\apex\App();
            } else {
                $app = new df\core\App();
            }
        }

        return $app;
    }


    /**
     * Quick dump
     */
    function dump(...$vars): void
    {
        debug\dumper\Handler::dump(...$vars);
    }

    /**
     * Cry about a method not being complete
     */
    function incomplete(): void
    {
        $call = debug\StackFrame::create(1);

        throw df\Error::EImplementation(
            $call->getSignature().' has not been completed yet!'
        );
    }

    /**
     * Direct facade for generating IError based exceptions
     */
    function Error($message, ?array $params=[], $data=null): IError
    {
        return error\Factory::create(
            null,
            [],
            $message,
            $params,
            $data
        );
    }
}
