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
    use Df\Core\App;
    use Df\Core\Config\Env;

    use Composer\Autoload\ClassLoader;
    use DecodeLabs\Glitch;

    use RuntimeException;

    define('Df\\START', microtime(true));

    /**
     * Initial setup
     */
    function setup(string $basePath=null): void
    {
        /* Ensure this only ever gets called once */
        static $started;

        if ($started) {
            throw new RuntimeException(
                'Df has already been bootstrapped'
            );
        }

        $started = true;

        /* Use reflection to get a handle on vendor path
         * and by extension, base path */
        if ($basePath === null) {
            $class = new \ReflectionClass(ClassLoader::class);
            [$basePath,] = explode('/vendor/', (string)$class->getFileName(), 2);
        }

        /* Make basePath available globally */
        define('Df\\BASE_PATH', $basePath);

        Glitch::setStartTime(Df\START)
            ->registerAsErrorHandler()
            ->registerPathAliases([
                'vendor' => Df\BASE_PATH.'/vendor',
                'app' => Df\BASE_PATH,
                'df-base' => __DIR__
            ]);
    }

    /**
     * Initial bootstrap
     */
    function bootstrap(string $basePath=null): App
    {
        Df\setup($basePath);

        /* Manually load App class from base path */
        if (file_exists($basePath.'/App.php')) {
            require_once $basePath.'/App.php';
        }

        $app = Df\app();
        $app->bootstrap();

        return $app;
    }


    /**
     * Get active app instance
     */
    function app(): App
    {
        static $app;

        if (!isset($app)) {
            if (class_exists('Df\\Apex\\App', true)) {
                $app = new Df\Apex\App();
            } else {
                $app = new Df\Core\App\Generic();
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
