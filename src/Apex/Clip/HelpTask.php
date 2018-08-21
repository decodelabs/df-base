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
use Df\Clip\Context;
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
        $request = $this->request->withPath($this['path'])
            ->withCommandParams([]);
        $context = new Context($this->app, $request, $this->shell);
        $task = Base::load($context);

        $factory = $this->app[Factory::class];
        $command = $factory->newCommand($request->getPath());

        $task->setup($command);
        $command->renderHelp($this->shell);
    }
}
