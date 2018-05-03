<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\core\error;

use df;

interface IReporter
{
    public function reportException(\Throwable $exception);
}
