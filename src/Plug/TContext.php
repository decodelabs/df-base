<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Plug;

use Df;
use Df\Core\IApp;
use Df\Plug\IHelper;
use Df\Plug\IGlobalHelper;

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


        return $this->{$name} = $this->loadHelper($name);
    }

    /**
     * Load helper
     */
    public function loadHelper(string $name): IHelper
    {
        $class = '\\Df\\Plug\\'.ucfirst($name);

        if (!class_exists($class, true)) {
            throw \Glitch::ENotFound([
                'Helper '.$name.' could not be found'
            ]);
        }

        $output = $this->app->newInstanceOf($class, [
            'context' => $this
        ], IHelper::class);

        if ($output instanceof IGlobalHelper) {
            $this->app->bindShared($class, $output);
        }

        return $output;
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
