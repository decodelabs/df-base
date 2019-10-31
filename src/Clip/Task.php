<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Clip;

use DecodeLabs\Terminus\Command\Definition;

interface Task extends \ArrayAccess
{
    public function setArgs(array $args): Task;
    public function getArgs(): array;
    public function getArg(string $name);

    public function setup(Definition $command): void;
    public function dispatch();
}
