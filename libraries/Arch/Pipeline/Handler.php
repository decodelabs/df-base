<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch\Pipeline;

use Df;

use Df\Core\IApp;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Handler implements IHandler
{
    protected $app;
    protected $areaMaps = [];
    protected $routers = [];

    /**
     * Construct with app
     */
    public function __construct(IApp $app)
    {
        $this->app = $app;
    }

    /**
     * Load list of string maps
     */
    public function loadAreaMaps(array $maps): IHandler
    {
        foreach ($maps as $area => $uri) {
            $map = new AreaMap($area, $uri);
            $this->addAreaMap($map);
        }

        return $this;
    }

    /**
     * Add area map to stack
     */
    public function addAreaMap(AreaMap $map): IHandler
    {
        $area = $map->getArea();

        if (isset($this->areaMaps[$area])) {
            throw Df\Error::ELogic(
                'Area "'.$area.'" has already been mapped'
            );
        }

        $this->areaMaps[$area] = $map;
        return $this;
    }




    /**
     * Load routers from list of packages
     */
    public function loadRouters(array $packages): IHandler
    {
        $class = '\\Df\\Apex\\Http\\'.ucfirst($area).'\\'.ucfirst($package).'Router';

        if (!class_exists($class, true)) {
            return $this;
        }

        $router = new $class();
        $this->addRouter($router);

        return $this;
    }

    /**
     * Add router to the stack
     */
    public function addRouter(Router $router): IHandler
    {
        $this->routers[get_class($router)] = $router;
        return $this;
    }




    /**
     * Invoke the callable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$response = $this->routeIn($request)) {
            return $handler->handle($request);
        }

        return $response;
    }


    /**
     * Build list of routes and dispatch
     */
    public function routeIn(ServerRequestInterface $request): ?ResponseInterface
    {
        // Make sure area maps make sense
        if (empty($this->areaMaps)) {
            throw Df\Error::ELogic(
                'No area maps have been defined'
            );
        }

        if (!isset($this->areaMaps['front']) && !isset($this->areaMaps['*'])) {
            throw Df\Error::EDefinition(
                'No default area map (front or *) has been defined'
            );
        }


        // Map original uri to an area mount
        $uri = $request->getUri();
        $url = $uri->getAuthority().$uri->getPath();
        $path = null;
        $params = [];

        foreach (array_reverse($this->areaMaps) as $area => $map) {
            if (null !== ($path = $map->matches($url, $params))) {
                if ($area === '*') {
                    if (preg_match('#^/~([^/]+)(/.*)?$#', $path, $matches)) {
                        $area = $matches[1];
                    } else {
                        if (isset($this->areaMaps['front'])) {
                            continue;
                        }

                        $area = 'front';
                    }
                }

                break;
            }
        }


        // No path? domain didn't match, move along
        if ($path === null) {
            return null;
        }


        // Load and test routes
        $method = $request->getMethod();
        $route = null;

        foreach ($this->routers as $router) {
            if ($route = $router->matchIn($method, $path)) {
                break;
            }
        }

        if ($route === null) {
            return null;
        }

        return $route->dispatch($request);
    }
}
