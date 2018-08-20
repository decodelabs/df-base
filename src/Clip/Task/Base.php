<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip\Task;

use Df;
use Df\Clip\IRequest;

use Df\Clip\ITask;
use Df\Clip\ICommand;
use Df\Plug\TContextProxy;

abstract class Base implements ITask
{
    use TContextProxy;

    /**
     * Prepare command
     */
    public function setup(ICommand $command): void
    {
    }
}
