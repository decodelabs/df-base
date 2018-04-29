<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\core\kernel;

use df;
use df\core;

class Console implements IConsole
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
