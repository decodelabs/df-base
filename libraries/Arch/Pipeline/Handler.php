<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch\Pipeline;

use Df;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Handler implements IHandler
{
    protected $areaMaps = [];


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
     * Invoke the callable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$request = $this->mapRequest($request)) {
            return $handler->handle($request);
        }

        $area = $request->getUri()->getHost();

        // TODO: route to Arch Request
        dd($request, $area);
    }


    /**
     * Map request input request to an area via a matched base path
     * The resulting request abstracts the input URL across multiple environments
     */
    protected function mapRequest(ServerRequestInterface $request): ?ServerRequestInterface
    {
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

        $uri = $request->getUri();
        $url = $uri->getAuthority().$uri->getPath();

        foreach (array_reverse($this->areaMaps) as $area => $map) {
            if (null !== ($outUri = $map->matches($url, $params))) {
                if ($area === '*') {
                    if (preg_match('#^/~([^/]+)(/.*)?$#', $outUri, $matches)) {
                        $area = $matches[1];
                    } else {
                        if (isset($this->areaMaps['front'])) {
                            continue;
                        }

                        $area = 'front';
                    }
                }

                $newUri = $uri
                    ->withHost($area)
                    ->withPort(null)
                    ->withPath($outUri);

                $newRequest = $request
                    ->withUri($newUri)
                    ->withRequestTarget($outUri);

                foreach ($params as $key => $value) {
                    $newRequest = $newRequest->withAttribute($key, $value);
                }

                return $newRequest;
            }
        }

        return null;
    }
}
