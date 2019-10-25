<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip\Command;

use Df\Clip\ICommand;
use Df\Clip\Command;
use Df\Clip\Command\IRequest;
use Df\Clip\Command\CliRequest;

class Factory
{
    /**
     * Generate IRequest from global environment variables
     */
    public function fromEnvironment(
        array $server=null,
        array $argv=null
    ): IRequest {
        $server = $this->prepareServerData($server ?? $_SERVER);
        $argv = $argv ?? $_SERVER['argv'];
        $script = array_shift($argv);
        $path = array_shift($argv);

        return new CliRequest(
            $server,
            $path,
            $argv,
            $script
        );
    }

    /**
     * Normalize $_SERVER or equivalent
     */
    public function prepareServerData(array $server): array
    {
        // Do anything that needs to be done here
        return $server;
    }


    /**
     * Convert request to bare command
     */
    public function requestToCommand(IRequest $request): ICommand
    {
        return $this->newCommand($request->getPath());
    }

    /**
     * Create a new bare command
     */
    public function newCommand(string $path): ICommand
    {
        return (new Command($path))
            ->addArgument('-help|h', 'Get help for this task');
    }
}
