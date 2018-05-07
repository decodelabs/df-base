<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Error\Renderer;

use Df;

use Df\Core\Error\IRenderer;

class Dump implements IRenderer
{
    /**
     * Report a caught exception
     */
    public function renderException(\Throwable $exception)
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        echo 'UNCAUGHT EXCEPTION';
        dd($exception);
    }
}
