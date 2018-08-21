<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Apex\Clip;

use Df;
use Df\Clip\ITask;
use Df\Clip\Task\Base;
use Df\Clip\ICommand;
use Df\Clip\Command\Factory;

class HelpTask extends Base
{
    /**
     * Prepare command
     */
    public function setup(ICommand $command): void
    {
        $command
            ->setHelp('Get instructions for any task in your app')
            ->addArgument('path=help', 'Path to the target task');
    }

    /**
     * Execute
     */
    public function dispatch()
    {
        $parts = array_map('ucfirst', explode('/', $path = $this['path']));
        $class = '\\Df\\Apex\\Clip\\'.implode('\\', $parts).'Task';

        if (!class_exists($class, true)) {
            $this->error('Task not found: '.$path);
            return 2;
        }

        $factory = $this->app[Factory::class];
        $command = $factory->newCommand($path);

        $task = $this->app->newInstanceOf($class, [
            'context' => $this->context,
        ], ITask::class);

        $task->setup($command);
        $command->renderHelp($this->shell);
    }
}
