<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch;

use Df\Core\App;
use Df\Arch\Route;
use Df\Arch\Uri;

use Df\Http\Message\Generator;
use Df\Http\Response\Stream;
use Df\Http\Response\Text;
use Df\Http\Response\Html;
use Df\Http\Response\Json;
use Df\Http\Response\Redirect;
use Df\Http\Response\Proxy;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use DecodeLabs\Collections\ArrayProvider;
use DecodeLabs\Tagged\Markup;
use DecodeLabs\Exceptional;

trait RouteTrait
{
    protected $path;
    protected $pattern;
    protected $params = [];
    protected $matchKeys = [];


    /**
     * Mix in params from area maps
     */
    public function mergeParams(array $params): Route
    {
        $this->params = array_merge($params, $this->params);
        return $this;
    }


    /**
     * Convert arch uri to http uri path string
     */
    public function routeOut(Uri $uri): string
    {
        $query = $uri->getQuery();

        $path = preg_replace_callback('#{([a-zA-Z0-9\-_]+)([/\?]*)}#', function ($matches) use ($query, $uri) {
            $value = $query[$matches[1]];
            unset($query[$matches[1]]);

            if ($value == '') {
                throw Exceptional::UnexpectedValue(
                    'Route out '.$uri.' requires "'.$matches[1].'" in the query'
                );
            }

            return $value;
        }, $this->path);

        return $path;
    }


    /**
     * Get internal URI representation of request URI
     */
    public function buildUri(ServerRequestInterface $request): Uri
    {
        $area = $request->getAttribute('area', 'front');
        $attributes = $request->getAttributes();
        unset($attributes['area']);

        $output = new Uri($this->getRouteType().'://~'.$area.'/'.ltrim($this->getRoutePath(), '/'));
        $output->setQuery($request->getUri()->getQuery());
        $output->getQuery()->merge(array_merge($attributes, $this->params));

        return $output;
    }


    /**
     * Compile pattern and match path
     */
    protected function matchPath(string $requestPath): ?Route
    {
        if ($this->pattern === null) {
            $this->preparePattern();
        }

        $requestPath = '/'.ltrim($requestPath, '/');

        if (!preg_match('#^'.$this->pattern.'$#', $requestPath, $matches)) {
            return null;
        }

        $output = clone $this;

        foreach ($output->matchKeys as $rep => $key) {
            $value = $matches[$rep];

            if ($value === '') {
                $value = null;
            }

            $output->params[$key[0]] = $value;
        }

        return $output;
    }

    /**
     * Prepare pattern and match keys
     */
    protected function preparePattern(): void
    {
        $this->params = [];
        $this->matchKeys = [];
        $pattern = '/'.ltrim($this->path, '/');

        $pattern = (string)preg_replace_callback('#{([a-zA-Z0-9\-_]+)([/\?]*)}#', function ($matches) {
            $rep = 'r'.count($this->params);
            $key = $matches[1];
            $greedy = $optional = false;

            if (isset($matches[2])) {
                foreach (str_split($matches[2]) as $modifier) {
                    switch ($modifier) {
                        case '/': $greedy = true; break;
                        case '?': $optional = true; break;
                    }
                }
            }

            if (array_key_exists($key, $this->params)) {
                throw Exceptional::UnexpectedValue(
                    'Area map key {'.$key.'} has been used more than once'
                );
            }

            $this->params[$key] = null;
            $this->matchKeys[$rep] = [$key, $greedy, $optional];
            return '{'.$rep.'}';
        }, $pattern);

        $pattern = str_replace(['\{', '\}'], ['{', '}'], preg_quote($pattern));

        if (empty($pattern)) {
            $pattern = '/';
        }

        $pattern = preg_replace_callback('/{(r[0-9]+)}/', function ($matches) {
            $rep = $matches[1];

            $output = '(?P<'.$rep.'>';

            if ($this->matchKeys[$rep][1]) {
                $output .= '.';
            } else {
                $output .= '[^/]';
            }

            if ($this->matchKeys[$rep][2]) {
                $output .= '*';
            } else {
                $output .= '+';
            }

            $output .= ')';
            return $output;
        }, $pattern);

        $this->pattern = $pattern;
    }


    /**
     * Ensure output is ResponseInterface
     */
    protected function normalizeResponse($output, App $app): ResponseInterface
    {
        if ($output instanceof \Closure) {
            $output = $app->call($output);
        }

        if ($output instanceof \Generator) {
            return new Stream(
                new Generator($output),
                200,
                ['content-type' => 'text/plain; charset=utf-8',]
            );
        }

        if ($output instanceof Proxy) {
            $output = $output->toHttpResponse();
        }

        if ($output instanceof ResponseInterface) {
            return $output;
        }

        if ($output instanceof UriInterface) {
            return new Redirect($output);
        }

        if ($output instanceof Markup) {
            return new Html((string)$output);
        }

        if ($output instanceof ArrayProvider) {
            $output = $output->toArray();
        }

        if (is_array($output) || $output instanceof \JsonSerializable) {
            return new Json($output);
        }

        if (is_scalar($output)) {
            return new Text((string)$output);
        }

        throw Exceptional::UnexpectedValue(
            'Don\'t know how to handle response',
            null,
            $output
        );
    }
}
