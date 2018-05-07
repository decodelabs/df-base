<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Error;

use Df;

interface IRenderer
{
    public function renderException(\Throwable $exception);
}
