<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Arch;

use Df\Core\IApp;

trait NodeTrait
{
    protected $app;

    /**
     * Construct with app
     */
    public function __construct(IApp $app)
    {
        $this->app = $app;
    }
}
