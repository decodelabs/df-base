<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch\Route;

use Df;
use Df\Core\IApp;
use Df\Arch\IRoute;

use Df\Data\IArrayProvider;

use Df\Http\Message\Generator;
use Df\Http\Response\Stream;
use Df\Http\Response\Text;
use Df\Http\Response\Json;
use Df\Http\Response\Redirect;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;

trait TRoute
{
    protected $path;
    protected $pattern;
    protected $params = [];
    protected $matchKeys = [];

    /**
     * Mix in params from area maps
     */
    public function mergeParams(array $params): IRoute
    {
        $this->params = array_merge($params, $this->params);
        return $this;
    }


    /**
     * Compile pattern and match path
     */
    protected function matchPath(string $requestPath): ?IRoute
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
            $output->params[$key[0]] = $matches[$rep];
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

        $pattern = preg_replace_callback('/{([a-zA-Z0-9\-_\/\?]*?)}/', function ($matches) {
            $rep = 'r'.count($this->params);
            $key = $matches[1];
            $greedy = $optional = false;

            if (substr($key, -1) == '/') {
                $key = substr($key, 0, -1);
                $greedy = true;
            }

            if (substr($key, -1) == '?') {
                $key = substr($key, 0, -1);
                $optional = true;
            }

            if (array_key_exists($key, $this->params)) {
                throw Df\Error::EUnexpectedValue(
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
    protected function normalizeResponse($output, IApp $app): ResponseInterface
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

        if ($output instanceof UriInterface) {
            return new Redirect($output);
        }

        if ($output instanceof IArrayProvider) {
            $output = $output->toArray();
        }

        if (is_array($output) || $output instanceof \JsonSerializable) {
            return new Json($output);
        }

        if (is_scalar($output)) {
            return new Text((string)$output);
        }

        throw Df\Error::EUnexpectedValue(
            'Don\'t know how to handle response',
            null,
            $output
        );
    }
}
