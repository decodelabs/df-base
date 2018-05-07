<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch\Pipeline;

use Df;

use Df\Core\IApp;

use Df\Arch\Uri as ArchUri;
use Df\Arch\Context;

use Df\Http\Uri as HttpUri;
use Df\Http\Response\Redirect;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Handler implements IHandler
{
    protected $app;
    protected $areaMaps = [];
    protected $routerBundles = [];
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
     * Set list of packages to load routers from
     */
    public function setRouterBundles(array $bundles): IHandler
    {
        $this->routerBundles = $bundles;
        return $this;
    }


    /**
     * Load routers from list of packages
     */
    public function loadRouters(string $area): IHandler
    {
        $this->routers[$area] = [];

        foreach ($this->routerBundles as $bundle) {
            $class = '\\Df\\Apex\\Http\\'.ucfirst($area).'\\'.ucfirst($bundle->getName()).'Router';

            if (!class_exists($class, true)) {
                continue;
            }

            $this->routers[$area][] = new $class($area);
        }

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
        $url = $uri->getAuthority().rawurldecode($uri->getPath());
        $scheme = $uri->getScheme();
        $path = null;
        $params = [];

        foreach (array_reverse($this->areaMaps) as $area => $map) {
            if (null !== ($path = $map->matches($url, $params))) {
                if ($area === '*') {
                    if (preg_match('#^/~([^/]+)(/.*)?$#', $path, $matches)) {
                        $area = $matches[1];
                        $path = $matches[2] ?? '/';
                    } else {
                        if (isset($this->areaMaps['front'])) {
                            continue;
                        }

                        $area = 'front';
                    }
                }

                // Make sure scheme matches
                $mapScheme = $map->getScheme();

                if ($mapScheme !== $scheme && $mapScheme === 'https') {
                    $redirect = $uri->withScheme($map->getScheme());
                    return new Redirect($redirect);
                }

                break;
            }
        }

        // No path? domain didn't match, move along
        if ($path === null) {
            return null;
        }


        // Add params a attributes
        $request = $request->withAttribute('area', $area);

        foreach ($params as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }



        // Use this instance as global http request
        $this->app->{'http.request.server'}->setInstance($request);


        // Load and test routes
        $method = $request->getMethod();
        $route = null;

        if (!isset($this->routers[$area])) {
            $this->loadRouters($area);
        }

        foreach ($this->routers[$area] as $router) {
            if ($route = $router->matchIn($method, $path)) {
                $uri = $route->buildUri($request);
                $context = new Context($this->app, $uri, $request);

                return $route->dispatch($context);
            }
        }


        // No match, try with or without trailing slash
        if (substr($path, -1) == '/') {
            $path = substr($path, 0, -1);
            $cut = true;
        } else {
            $path .= '/';
            $cut = false;
        }

        foreach ($this->routers[$area] as $router) {
            if ($route = $router->matchIn($method, $path)) {
                $uri = $request->getUri();
                $newPath = $uri->getPath();

                if ($cut) {
                    $newPath = substr($newPath, 0, -1);
                } else {
                    $newPath .= '/';
                }

                return new Redirect($uri->withPath($newPath));
            }
        }


        // Fail
        return null;
    }


    /**
     * Convert internat request uri to HTTP uri
     */
    public function routeOut(ArchUri $uri): HttpUri
    {
        $area = $uri->getArea();
        $route = null;

        if (!isset($this->routers[$area])) {
            $this->loadRouters($area);
        }

        foreach ($this->routers[$area] as $router) {
            if ($route = $router->matchOut($uri)) {
                break;
            }
        }

        if (!$route) {
            throw Df\Error::EUnexpectedValue(
                'Arch uri '.$uri.' did not match any routes'
            );
        }

        $map = $this->areaMaps[$area] ?? $this->areaMaps['*'] ?? null;

        if (!$map) {
            throw Df\Error::ERuntime(
                'No matching area maps to route out uri: '.$uri
            );
        }

        $uri = clone $uri;
        $path = $route->routeOut($uri);
        return $map->routeOut($uri, $path);
    }
}
