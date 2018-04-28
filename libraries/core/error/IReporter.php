<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\core\error;

use df;
use df\core;

interface IReporter
{
    public function reportException(\Throwable $exception);
}
