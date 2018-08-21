<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Clip;

use Df;

interface ITask extends \ArrayAccess
{
    public function setArgs(array $args): ITask;
    public function getArgs(): array;
    public function getArg(string $name);

    public function setup(ICommand $command): void;
    public function dispatch();
}
