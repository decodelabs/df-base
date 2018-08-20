<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Clip;

use Df;

interface ITask
{
    public function setup(ICommand $command): void;
    public function dispatch();
}
