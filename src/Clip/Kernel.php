<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip;

use Df\Core\IApp;
use Df\Core\Kernel\IConsole;

use Df\Clip\ITask;
use Df\Clip\Task\Base;
use Df\Clip\Command\Factory;
use Df\Clip\Command\IRequest;

use Df\Clip\Shell\Std;

class Kernel implements IConsole
{
    protected $app;

    /**
     * Setup with ref to $app
     */
    public function __construct(IApp $app)
    {
        $this->app = $app;
    }

    /**
     * Full stack wrapper around default behaviour
     */
    public function run(): void
    {
        $request = $this->prepareRequest();
        $status = $this->handle($request);
        $this->terminate($request, $status);
    }

    /**
     * Convert argv into shareable object
     */
    public function prepareRequest(): IRequest
    {
        return $this->app[IRequest::class];
    }


    /**
     * Load the task and run it
     */
    public function handle(IRequest $request): int
    {
        $context = new Context($this->app, $request, $shell = new Std());
        $task = Base::load($context);

        $factory = $this->app[Factory::class];
        $command = $factory->requestToCommand($request);
        $task->setup($command);

        try {
            $args = $command->apply($request);
        } catch (EUnexpectedValue $e) {
            $shell->writeLine();
            $context->render($e->getMessage(), 'error');
            $command->renderHelp($context);

            return 1;
        }

        $task->setArgs($args);

        if ($args['help'] ?? false) {
            $command->renderHelp($context);
            return 0;
        }


        $res = $task->dispatch();
        $status = null;

        if (is_int($res)) {
            $status = $res;
        } elseif ($res !== null) {
            $context->render($res);
        }

        if ($res instanceof \Generator) {
            $status = $res->getReturn();
        }

        if (!is_int($status)) {
            $status = 0;
        }

        return $status;
    }


    /**
     * Close down the app
     */
    public function terminate(IRequest $request, int $status=0): void
    {
        $this->app->terminate();
        exit($status);
    }
}
