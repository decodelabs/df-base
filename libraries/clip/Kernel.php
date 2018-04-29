<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\clip;

use df;
use df\clip;
use df\core\kernel\IConsole;

class Kernel implements IConsole
{
    protected $app;

    /**
     * Setup with ref to $app
     */
    public function __construct(core\IApp $app)
    {
        $this->app = $app;
    }

    /**
     * Full stack wrapper around default behaviour
     */
    public function run(): void
    {
        df\incomplete();
    }
}
