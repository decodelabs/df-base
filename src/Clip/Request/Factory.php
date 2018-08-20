<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip\Request;

use Df;
use Df\Clip\IRequest;

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
}
