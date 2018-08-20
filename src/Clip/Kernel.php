<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip;

use Df;

use Df\Core\IApp;
use Df\Core\Kernel\IConsole;

use Df\Clip\IRequest;
use Df\Clip\ITask;
use Df\Clip\IDispatcher;

class Kernel implements IConsole
{
    protected $app;
    protected $dispatcher;

    /**
     * Setup with ref to $app
     */
    public function __construct(IApp $app, IDispatcher $dispatcher)
    {
        $this->app = $app;
        $this->dispatcher = $dispatcher;
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
        $this->dispatcher->handle($request);
        return $this->dispatcher->getStatus();
        
        $parts = array_map('ucfirst', explode('/', $request->getPath()));
        $class = '\\Df\\Apex\\Clip\\'.implode('\\', $parts).'Task';

        if (!class_exists($class, true)) {
            throw Df\Error::ENotFound([
                'message' => 'Task not found: '.$request->getPath(),
                'data' => $request
            ]);
        }

        $task = $this->app->newInstanceOf($class, [], ITask::class);
        $res = $task->dispatch();

        foreach ($res as $outType => $output) {
            echo $output."\n";
        }

        $status = $res->getReturn();

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
