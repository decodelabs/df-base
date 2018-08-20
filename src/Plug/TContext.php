<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Plug;

use Df;
use Df\Core\IApp;

trait TContext
{
    protected $app;

    /**
     * Init with app
     */
    public function __construct(IApp $app)
    {
        $this->app = $app;
    }

    /**
     * Get protected member
     */
    public function __get(string $name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }

        // TODO: load helper
        dd($name);
    }

    /**
     * Temporary uri router
     */
    public function uri($uri): HttpUri
    {
        $handler = $this->app['arch.pipeline.handler'];
        return $handler->routeOut(ArchUri::instance($uri));
    }
}
