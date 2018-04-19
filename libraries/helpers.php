<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df;

use df;

define('df\\START', microtime(true));

function dump(...$vars)
{
    var_dump(...$vars);
    echo "\n\n".'<br /><pre class="xdebug-var-dump">Time: <strong>'.number_format((microtime(true) - df\START) * 1000, 2).' ms</strong></pre>';

    exit;
}


function Error($message, array $params=[], array $interfaces=[]): lang\error\IError
{
    return lang\error\Factory::create(
        null,
        $message,
        $params,
        $interfaces
    );
}
