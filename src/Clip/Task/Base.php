<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip\Task;

use Df;

use Df\Clip\ITask;
use Df\Clip\ICommand;
use Df\Clip\Command\IRequest;

use Df\Plug\TContextProxy;

abstract class Base implements ITask
{
    use TContextProxy;

    protected $args = [];

    /**
     * Set parsed arg list
     */
    public function setArgs(array $args): ITask
    {
        $this->args = $args;
        return $this;
    }

    /**
     * Get parsed arg list
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * Get a single arg
     */
    public function getArg(string $name)
    {
        return $this->args[$name] ?? null;
    }


    /**
     * Set via array access
     */
    public function offsetSet($key, $value)
    {
        $this->args[$key] = $value;
    }

    /**
     * Get via array access
     */
    public function offsetGet($key)
    {
        return $this->args[$key] ?? null;
    }

    /**
     * Check arg via array access
     */
    public function offsetExists($key)
    {
        return isset($this->args[$key]);
    }

    /**
     * Remove arg via array access
     */
    public function offsetUnset($key)
    {
        unset($this->args[$key]);
    }


    /**
     * Prepare command
     */
    public function setup(ICommand $command): void
    {
    }
}
