<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch\Pipeline;

use Df;

class AreaMap
{
    protected $area;
    protected $uri;

    protected $pattern;
    protected $matchKeys = [];

    /**
     * Init with area and uri
     */
    public function __construct(string $area, string $uri)
    {
        if (!preg_match('/^\*|[a-z]+$/', $area)) {
            throw Df\Error::EInvalidArgument(
                'Invalid area in area map: '.$area
            );
        }

        $this->area = ltrim($area, '~');
        $this->uri = $uri;
    }


    /**
     * Get area
     */
    public function getArea(): string
    {
        return $this->area;
    }

    /**
     * Get original URI
     */
    public function getUri(): string
    {
        return $this->uri;
    }


    /**
     * Prepare pattern and match keys
     */
    protected function preparePattern(): void
    {
        $this->matchKeys = [];

        $pattern = preg_replace_callback('/{([a-zA-Z0-9\-_]*?)}/', function ($matches) {
            $rep = 'r'.count($this->matchKeys);
            $key = $matches[1];

            if (in_array($key, $this->matchKeys)) {
                throw Df\Error::EUnexpectedValue(
                    'Area map key {'.$key.'} has been used more than once'
                );
            }

            $this->matchKeys[$rep] = $key;
            return '{'.$rep.'}';
        }, $this->uri);

        $pattern = str_replace(['\{', '\}'], ['{', '}'], preg_quote(rtrim($pattern, '/')));

        $pattern = preg_replace_callback('/{(r[0-9]+)}/', function ($matches) {
            return '(?P<'.$matches[1].'>\w+)';
        }, $pattern);

        $this->pattern = $pattern;
    }


    /**
     * Check for match
     */
    public function matches(string $inputUri, &$params=[]): ?string
    {
        if ($this->pattern === null) {
            $this->preparePattern();
        }

        if (!preg_match('#^'.$this->pattern.'(?P<path>/.*)?$#', $inputUri, $matches)) {
            return null;
        }

        $params = [];

        foreach ($this->matchKeys as $rep => $key) {
            $params[$key] = $matches[$rep];
        }

        return $matches['path'] ?? '/';
    }
}
