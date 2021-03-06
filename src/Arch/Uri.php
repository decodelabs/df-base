<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Arch;

use DecodeLabs\Collections\Tree;
use DecodeLabs\Collections\Tree\NativeMutable as MutableTree;

use DecodeLabs\Glitch\Dumpable;
use DecodeLabs\Exceptional;

class Uri implements \ArrayAccess, Dumpable
{
    const DELIMITERS = '!\$&\'\(\)\*\+,;=';
    const VALID_CHARACTERS = 'a-zA-Z0-9_\-\.~\pL';

    protected $routeType;
    protected $area;
    protected $path;
    protected $query;
    protected $fragment;


    /**
     * Create new instance from parts
     */
    public static function create(
        string $routeType,
        string $area='front',
        string $path=null,
        $query=null,
        string $fragment=null
    ): Uri {
        $output = new static();

        $output->routeType = $output->prepareRouteType($routeType);
        $output->area = $output->prepareArea($area);
        $output->path = $output->preparePath($path);
        $output->query = $output->prepareQuery($query);
        $output->fragment = $output->prepareFragment($fragment);

        return $output;
    }

    /**
     * Ensure value is a Uri
     */
    public static function instance($value): Uri
    {
        if (is_string($value)) {
            return new static($value);
        }

        if ($value instanceof static) {
            return $value;
        }

        throw Exceptional::InvalidArgument(
            'Invalid Arch Uri instance',
            null,
            $value
        );
    }


    /**
     * Init as string
     */
    public function __construct(string $uri=null)
    {
        if ($uri === null) {
            return;
        }

        $this->parse($uri);
    }

    /**
     * Parse uri into parts
     */
    protected function parse(string $uri): void
    {
        $parts = explode('://', $uri, 2);
        $path = ltrim((string)array_pop($parts), '/');
        $scheme = array_shift($parts);

        if (substr($path, 0, 1) != '~') {
            $path = '~front/'.$path;
        }

        if (empty($scheme)) {
            $scheme = 'any';
        }

        $uri = $scheme.'://'.$path;
        $parts = parse_url($uri);

        if ($parts === false) {
            throw Exceptional::InvalidArgument(
                'Unable to parse uri',
                null,
                $uri
            );
        }

        $this->routeType = $this->prepareRouteType($parts['scheme'] ?? null);
        $this->area = $this->prepareArea($parts['host'] ?? null);
        $this->path = $this->preparePath($parts['path'] ?? null);
        $this->query = $this->prepareQuery($parts['query'] ?? null);
        $this->fragment = $this->prepareFragment($parts['fragment'] ?? null);
    }


    /**
     * Get parts
     */
    public function __get(string $part)
    {
        if (isset($this->{$part})) {
            return $this->{$part};
        }

        throw Exceptional::Logic(
            'Arch\\Uri does not have member "'.$part.'"'
        );
    }

    /**
     * Ensure query gets cloned
     */
    public function __clone()
    {
        $this->query = clone $this->query;
    }



    /**
     * Set route type (uri schema)
     */
    public function setRouteType(string $type): Uri
    {
        $this->routeType = $this->prepareRouteType($type);
        return $this;
    }

    /**
     * Get route type (uri schema)
     */
    public function getRouteType(): string
    {
        return $this->routeType;
    }


    /**
     * Set area (defaults to ~front)
     */
    public function setArea(?string $area): Uri
    {
        $this->area = $this->prepareArea($area);
        return $this;
    }

    /**
     * Get area
     */
    public function getArea(): string
    {
        return $this->area;
    }


    /**
     * Set uri path
     */
    public function setPath(?string $path): Uri
    {
        $this->path = $this->preparePath($path);
        return $this;
    }

    /**
     * Get uri path
     */
    public function getPath(): string
    {
        return $this->path;
    }


    /**
     * Set query tree - parses strings, converts to mutable tree collection
     */
    public function setQuery($query): Uri
    {
        $this->query = $this->prepareQuery($query);
        return $this;
    }

    /**
     * Merge query data
     */
    public function mergeQuery($query): Uri
    {
        $this->query->merge($this->prepareQuery($query));
        return $this;
    }

    /**
     * Get query tree
     */
    public function getQuery(): Tree
    {
        return $this->query;
    }


    /**
     * Set fragment
     */
    public function setFragment(?string $fragment): Uri
    {
        $this->fragment = $this->prepareFragment($fragment);
        return $this;
    }

    /**
     * Get fragment
     */
    public function getFragment(): ?string
    {
        return $this->fragment;
    }



    /**
     * Get parts to make up routeId
     */
    public function getRouteId(): string
    {
        $output = $this->routeType.'://';
        $output .= ltrim($this->path, '/');
        return $output;
    }



    /**
     * Prepare route type (schema)
     */
    protected function prepareRouteType(?string $type): string
    {
        if (empty($type)) {
            $type = 'name';
        }

        if (preg_match('/[^a-zA-Z0-9\-]/', $type)) {
            throw Exceptional::InvalidArgument(
                'Invalid Arch\Uri route type: '.$type
            );
        }

        return lcfirst($type);
    }

    /**
     * Prepare area key
     */
    protected function prepareArea(?string $area): string
    {
        if (empty($area)) {
            $area = 'front';
        }

        $area = lcfirst(ltrim($area, '~'));

        if (preg_match('/[^a-zA-Z0-9\-]/', $area)) {
            throw Exceptional::InvalidArgument(
                'Invalid Arch\Uri route area: '.$area
            );
        }

        return $area;
    }

    /**
     * Prepare path string
     */
    protected static function preparePath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (strpos($path, '?') !== false) {
            throw Exceptional::InvalidArgument(
                'Invalid path, must not contain query string'
            );
        }

        if (strpos($path, '#') !== false) {
            throw Exceptional::InvalidArgument(
                'Invalid path, must not contain fragment'
            );
        }

        $path = (string)preg_replace_callback(
            '#(?:[^'.self::VALID_CHARACTERS.')(:@&=\+\$,/;%]+|%(?![A-Fa-f0-9]{2}))#u',
            function ($matches) {
                return rawurlencode($matches[0]);
            },
            $path
        );

        $path = '/'.ltrim($path, '/');
        return $path;
    }

    /**
     * Prepare query tree object
     */
    protected function prepareQuery($query): Tree
    {
        if ($query instanceof Tree) {
            return $query;
        } elseif (empty($query)) {
            return new MutableTree();
        } elseif (is_string($query)) {
            return MutableTree::fromDelimitedString($query);
        }

        return new MutableTree($query);
    }

    /**
     * Prepare fragment
     */
    protected static function prepareFragment(?string $fragment): ?string
    {
        if ($fragment === null || !strlen((string)$fragment)) {
            return null;
        }

        return preg_replace_callback(
            '#(?:[^'.self::VALID_CHARACTERS.self::DELIMITERS.'%:@/\?]+|%(?![A-Fa-f0-9]{2}))#u',
            function ($matches) {
                return rawurlencode($matches[0]);
            },
            $fragment
        );
    }


    /**
     * Convert to string
     */
    public function __toString(): string
    {
        $output = $this->routeType.'://';

        if ($this->area !== 'front') {
            $output .= '~'.$this->area.'/';
        }

        if ($this->path !== null) {
            $output .= ltrim($this->path, '/');
        }

        $query = $this->query->toDelimitedString();

        if (!empty($query)) {
            $output .= '?'.$query;
        }

        if ($this->fragment !== null) {
            $output .= '#'.$this->fragment;
        }

        return $output;
    }



    /**
     * Set query value
     */
    public function offsetSet($key, $value): void
    {
        $this->query->offsetSet($key, $value);
    }

    /**
     * Get query value
     */
    public function offsetGet($key)
    {
        return $this->query->offsetGet($key);
    }

    /**
     * Query has node
     */
    public function offsetExists($key): bool
    {
        return $this->query->offsetExists($key);
    }

    /**
     * Remove query value
     */
    public function offsetUnset($key): void
    {
        $this->query->offsetUnset($key);
    }


    /**
     * Export for dump inspection
     */
    public function glitchDump(): iterable
    {
        yield 'definition' => $this->__toString();
    }
}
