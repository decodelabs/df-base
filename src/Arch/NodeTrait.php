<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Arch;

use Df\Core\App;

trait NodeTrait
{
    protected $app;

    /**
     * Construct with app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }
}
