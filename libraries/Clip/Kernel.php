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
        Df\incomplete();
    }
}
