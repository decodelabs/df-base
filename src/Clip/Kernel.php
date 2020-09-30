<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip;

use Df\Core\App;
use Df\Core\Kernel\Console as ConsoleKernel;

use Df\Clip\Task\Base;

use DecodeLabs\Terminus\Cli;
use DecodeLabs\Terminus\Command\Request;

class Kernel implements ConsoleKernel
{
    protected $app;

    /**
     * Setup with ref to $app
     */
    public function __construct(App $app)
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
    public function prepareRequest(): Request
    {
        return $this->app[Request::class];
    }


    /**
     * Load the task and run it
     */
    public function handle(Request $request): int
    {
        Cli::replaceSession($request);
        $task = Base::load($this->app, $request);
        $session = Cli::prepareCommand([$task, 'setup']);
        $command = $session->getCommandDefinition();

        try {
            $args = $command->apply($request);
        } catch (\Throwable $e) {
            Cli::writeLine();
            Cli::{'error'}($e->getMessage());
            $command->renderHelp($session);

            return 1;
        }

        $task->setArgs($args);

        if ($args['help'] ?? false) {
            $command->renderHelp($session);
            return 0;
        }


        $res = $task->dispatch();
        $status = null;

        if (is_int($res)) {
            $status = $res;
        } elseif ($res !== null) {
            Cli::writeLine($res);
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
    public function terminate(Request $request, int $status=0): void
    {
        $this->app->terminate();
        exit($status);
    }
}
