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
        lang\debug\dumper\Handler::dump(...$vars);
    }

    /**
     * Cry about a method not being complete
     */
    function incomplete(): void
    {
        $call = lang\debug\StackCall::create(1);

        throw df\Error::EImplementation(
            $call->getSignature().' has not been completed yet!'
        );
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
