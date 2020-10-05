<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Apex\Clip;

use Df\Clip\Task\Base;
use DecodeLabs\Terminus as Cli;
use DecodeLabs\Terminus\Command\Definition;

class HelpTask extends Base
{
    /**
     * Prepare command
     */
    public function setup(Definition $command): void
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
        $path = $this['path'];
        $request = Cli::newRequest([$path]);
        $task = Base::load($this->app, $request);
        $command = Cli::newCommandDefinition($path);
        $task->setup($command);
        $command->renderHelp(Cli::getSession());
    }
}
