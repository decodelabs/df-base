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
    use df\lang\dumper;
    use Symfony\Component\VarDumper\VarDumper;

    if (!function_exists('dd')) {
        /**
         * Super quick global dump & die
         */
        function dd(...$vars): void
        {
            dumper\Handler::dumpDie(...$vars);
        }
    }

    if (!function_exists('dump')) {
        /**
         * Quick dump
         */
        function dump(...$vars): void
        {
            dumper\Handler::dump(...$vars);
        }
    } elseif (class_exists(VarDumper::class)) {
        VarDumper::setHandler([dumper\Handler::class, 'dump']);
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
    use df\lang\dumper;

    use Composer\Autoload\ClassLoader;

    define('df\\START', microtime(true));


    /**
     * Initial bootstrap
     */
    function bootstrap(string $basePath=null): core\IApp
    {
        /* Ensure this only ever gets called once */
        static $started;

        if ($started) {
            throw new \RuntimeException('df has already been bootstrapped');
        }

        $started = true;


        /* Use reflection to get a handle on vendor path
         * and by extension, base path */
        if ($basePath === null) {
            $class = new \ReflectionClass(ClassLoader::class);
            [$basePath,] = explode('/vendor/', $class->getFileName(), 2);
        }

        /* Make basePath available globally */
        define('df\\BASE_PATH', $basePath);

        /* Manually load App class from base path */
        if (file_exists($basePath.'/App.php')) {
            require_once $basePath.'/App.php';
        }

        $app = df\app();
        $app->bootstrap();
        define('df\\BOOTSTRAPPED', true);

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
     * Cry about a method not being complete
     */
    function incomplete(): void
    {
        $call = lang\stack\Frame::create(1);

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

    /**
     * Strip base path from path string
     */
    function stripBasePath(?string $path): ?string
    {
        if (!defined('df\\BASE_PATH') || $path === null) {
            return $path;
        }

        $parts = explode(df\BASE_PATH, $path, 2);
        return array_pop($parts);
    }
}
