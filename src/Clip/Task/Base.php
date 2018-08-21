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
use Df\Clip\Context;
use Df\Clip\Command\IRequest;

use Df\Plug\TContextProxy;
use Df\Flex\Formatter;

abstract class Base implements ITask
{
    use TContextProxy;

    protected $args = [];


    /**
     * Load a task
     */
    public static function load(Context $context): ITask
    {
        $parts = array_map(
            [Formatter::class, 'id'],
            explode('/', $context->request->getPath())
        );
        
        $class = '\\Df\\Apex\\Clip\\'.implode('\\', $parts).'Task';

        if (!class_exists($class, true)) {
            throw Df\Error::ENotFound([
                'message' => 'Task not found: '.$request->getPath(),
                'data' => $request
            ]);
        }

        return $context->app->newInstanceOf($class, [
            'context' => $context,
        ], ITask::class);
    }


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
