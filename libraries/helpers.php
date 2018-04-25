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
    use df\lang\error;
    use df\lang\debug;

    define('df\\START', microtime(true));

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
