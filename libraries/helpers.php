<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */


/**
 * global helpers
 */
namespace
{
    if (!function_exists('dd')) {
        /**
         * Super quick global dump
         */
        function dd(...$vars): void
        {
            http_response_code(500);

            foreach ($vars as $var) {
                df\lang\debug\dumper\Handler::dump($var);
            }

            die(1);
        }
    }
}


/**
 * df helper
 */
namespace df
{

    use df;
    use df\lang;

    define('df\\START', microtime(true));

    /**
     * Quick dump
     */
    function dump(): void
    {
        http_response_code(500);

        foreach ($vars as $var) {
            df\lang\debug\dumper\Handler::dump($var);
        }

        die(1);
    }

    /**
     * Direct facade for generating IError based exceptions
     */
    function Error($message, array $params=[], array $interfaces=[]): IError
    {
        return lang\error\Factory::create(
            null,
            $message,
            $params,
            $interfaces
        );
    }
}
