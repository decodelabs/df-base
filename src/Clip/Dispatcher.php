<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip;

use Df;
use Df\Core\IApp;

use Df\Clip\IRequest;
use Df\Clip\Shell\Std;

class Dispatcher implements IDispatcher
{
    protected $status = 0;
    protected $app;


    /**
     * Setup with ref to $app
     */
    public function __construct(IApp $app)
    {
        $this->app = $app;
    }


    /**
     * Load the task and run it
     */
    public function handle(IRequest $request): void
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

        $this->status = $status;
    }

    /**
     * Get current exit status
     */
    public function getStatus(): int
    {
        return $this->status;
    }
}
