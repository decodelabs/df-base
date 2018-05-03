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
     * Init with config...
     */
    public function __construct()
    {
        // TODO: Get this from config
        $devAreas = [
            '*' => 'df.test:8080/test/df-playground-/',
            'admin' => 'df.test:8080/test/df-playground-/admin/',
            'shared' => 'df.test:8080/test/df-playground-/~{name-test}/{stuff}',
            'devtools' => 'devtools.df.test:8080/test/df-playground-/'
        ];

        foreach ($devAreas as $area => $uri) {
            $map = new AreaMap($area, $uri);
            $this->areaMaps[$map->getArea()] = $map;
        }

        if (!isset($this->areaMaps['front']) && !isset($this->areaMaps['*'])) {
            throw Df\Error::EDefinition(
                'No default area map (front or *) has been defined'
            );
        }
    }


    /**
     * Invoke the callable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$request = $this->mapRequest($request)) {
            return $handler->handle($request);
        }

        // TODO: route to Arch Request
        dd($request);
    }


    /**
     * Map request
     */
    protected function mapRequest(ServerRequestInterface $request): ?ServerRequestInterface
    {
        $uri = $request->getUri();
        $url = $uri->getAuthority().$uri->getPath();

        foreach (array_reverse($this->areaMaps) as $area => $map) {
            if (null !== ($outUri = $map->matches($url, $params))) {
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
