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
            df\lang\debug\dumper\Handler::dump(...$vars);
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
    function dump(...$vars): void
    {
        df\lang\debug\dumper\Handler::dump(...$vars);
    }

    /**
     * Direct facade for generating IError based exceptions
     */
    function Error($message, ?array $params=[], $data=null): IError
    {
        return lang\error\Factory::create(
            null,
            [],
            $message,
            $params,
            $data
        );
    }
}
