<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Apex\Clip;

use Df;
use Df\Clip\ITask;
use Df\Plug\TContextProxy;

class HelpTask implements ITask
{
    use TContextProxy;

    /**
     * Execute
     */
    public function dispatch()
    {
        dd($this->request);
    }
}
