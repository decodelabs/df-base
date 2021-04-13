<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip\Task;

use Df\Core\App;
use Df\Clip\Task;

use DecodeLabs\Terminus\Command\Request;
use DecodeLabs\Terminus\Command\Definition;
use DecodeLabs\Exceptional;
use DecodeLabs\Dictum;

abstract class Base implements Task
{
    protected $args = [];
    protected $app;


    /**
     * Load a task
     */
    public static function load(App $app, Request $request): Task
    {
        if (empty($path = $request->getScript())) {
            throw Exceptional::UnexpectedValue(
                'Script path not set in request', null, $request
            );
        }

        $parts = array_map(
            function ($value) {
                return Dictum::id($value);
            },
            explode('/', (string)$path)
        );

        $class = '\\Df\\Apex\\Clip\\'.implode('\\', $parts).'Task';

        if (!class_exists($class, true)) {
            throw Exceptional::NotFound([
                'message' => 'Task not found: '.$path,
                'data' => $request
            ]);
        }

        $output = $app->newInstanceOf($class, [], Task::class);

        if (!$output instanceof Task) {
            throw Exceptional::Definition(
                'Task class does not implement Task interface', null, $output
            );
        }

        return $output;
    }

    /**
     * Init with app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }


    /**
     * Set parsed arg list
     */
    public function setArgs(array $args): Task
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
    public function setup(Definition $command): void
    {
    }
}
