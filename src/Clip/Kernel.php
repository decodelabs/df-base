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

        $parts = array_map('ucfirst', explode('/', $request->getPath()));
        $class = '\\Df\\Apex\\Clip\\'.implode('\\', $parts).'Task';

        if (!class_exists($class, true)) {
            throw Df\Error::ENotFound([
                'message' => 'Task not found: '.$request->getPath(),
                'data' => $request
            ]);
        }

        $task = $this->app->newInstanceOf($class, [
            'context' => $context,
        ], ITask::class);

        $res = $task->dispatch();
        $status = null;

        if (is_int($res)) {
            $status = $res;
        } elseif ($res !== null) {
            $shell->render($res);
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
