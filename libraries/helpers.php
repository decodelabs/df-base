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
    use Df\Lang\Dumper\Handler as DumpHandler;
    use Symfony\Component\VarDumper\VarDumper;

    if (!function_exists('dd')) {
        /**
         * Super quick global dump & die
         */
        function dd(...$vars): void
        {
            DumpHandler::dumpDie(...$vars);
        }
    }

    if (!function_exists('dump')) {
        /**
         * Quick dump
         */
        function dump(...$vars): void
        {
            DumpHandler::dump(...$vars);
        }
    } elseif (class_exists(VarDumper::class)) {
        VarDumper::setHandler([DumpHandler::class, 'dump']);
    }
}


/**
 * Df helper
 */
namespace Df
{

    use Df;
    use Df\Core\IApp;
    use Df\Lang\Error\Factory as ErrorFactory;

    use Composer\Autoload\ClassLoader;

    define('Df\\START', microtime(true));


    /**
     * Initial bootstrap
     */
    function bootstrap(string $basePath=null): IApp
    {
        /* Ensure this only ever gets called once */
        static $started;

        if ($started) {
            throw new \RuntimeException('Df has already been bootstrapped');
        }

        $started = true;


        /* Use reflection to get a handle on vendor path
         * and by extension, base path */
        if ($basePath === null) {
            $class = new \ReflectionClass(ClassLoader::class);
            [$basePath,] = explode('/vendor/', $class->getFileName(), 2);
        }

        /* Make basePath available globally */
        define('Df\\BASE_PATH', $basePath);

        /* Manually load App class from base path */
        if (file_exists($basePath.'/App.php')) {
            require_once $basePath.'/App.php';
        }

        $app = Df\app();
        $app->bootstrap();
        define('Df\\BOOTSTRAPPED', true);

        return $app;
    }


    /**
     * Get active app instance
     */
    function app(): IApp
    {
        static $app;

        if (!isset($app)) {
            if (class_exists('Df\Apex\App', true)) {
                $app = new Df\Apex\App();
            } else {
                $app = new Df\Core\App();
            }
        }

        return $app;
    }


    /**
     * Cry about a method not being complete
     */
    function incomplete(): void
    {
        $frame = lang\stack\Frame::create(1);

        throw Df\Error::EImplementation(
            $frame->getSignature().' has not been completed yet!'
        );
    }

    /**
     * Direct facade for generating IError based exceptions
     */
    function Error($message, ?array $params=[], $data=null): IError
    {
        return ErrorFactory::create(
            null,
            [],
            $message,
            $params,
            $data
        );
    }

    /**
     * Strip base path from path string
     */
    function stripBasePath(?string $path): ?string
    {
        if (!defined('Df\\BASE_PATH') || $path === null) {
            return $path;
        }

        $parts = explode(Df\BASE_PATH, $path, 2);
        return array_pop($parts);
    }
}
