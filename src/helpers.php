<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);

/**
 * Df helper
 */
namespace Df
{
    use Df;
    use Df\Core\IApp;
    use Df\Core\Config\Env;

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

        \Glitch::setAutoRegister(false);
        \Glitch::getContext()
            ->setStartTime(Df\START)
            ->registerPathAliases([
                'vendor' => Df\BASE_PATH.'/vendor',
                'app' => Df\BASE_PATH,
                'df-base' => __DIR__
            ]);


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
     * Get env config value
     */
    function env(): Env
    {
        return Df\app()[Env::class];
    }
}
