<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\core\error\reporter;

use df;
use df\core;

class Dump implements core\error\IReporter
{
    /**
     * Report a caught exception
     */
    public function reportException(\Throwable $exception)
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        echo 'UNCAUGHT EXCEPTION';
        dd($exception);
    }
}
