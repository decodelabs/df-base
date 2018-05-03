<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\http;

use df;
use df\link\IUri;
use df\data\Tree;

use Psr\Http\Message\UriInterface;

class Uri implements IUri
{
    const DELIMITERS = '!\$&\'\(\)\*\+,;=';
    const VALID_CHARACTERS = 'a-zA-Z0-9_\-\.~\pL';

    const SCHEMES = [
        'http' => 80,
        'https' => 443
    ];

    protected $scheme;
    protected $username;
    protected $password;
    protected $host;
    protected $port;
    protected $path;
    protected $query;
    protected $fragment;


    public static function create(
        string $scheme='http',
        string $username=null,
        string $password=null,
        string $host=null,
        int $port=null,
        string $path=null,
        string $query=null,
        string $fragment=null
    ) {
        $output = new static(null);

        $output->scheme = $output->prepareScheme($scheme);
        $output->username = $output->prepareUserInfo($username);
        $output->password = $output->prepareUserInfo($password);
        $output->host = $output->prepareHost($host);
        $output->port = $output->preparePort($port);
        $output->path = $output->preparePath($path);
        $output->query = $output->prepareQuery($query);
        $output->fragment = $output->prepareFragment($fragment);

        return $output;
    }


    /**
     * Create new instance with text uri
     */
    public function __construct(?string $uri)
    {
        if (!empty($uri)) {
            $this->parse($uri);
        }
    }


    /**
     * Split string into parts
     */
    protected function parse(string $uri): void
    {
        $parts = parse_url($uri);

        if ($parts === false) {
            throw df\Error::EInvalidArgument(
                'Unable to parse uri',
                null,
                $uri
            );
        }

        $this->scheme = $this->prepareScheme($parts['scheme'] ?? null);
        $this->username = $this->prepareUserInfo($parts['user'] ?? null);
        $this->password = $this->prepareUserInfo($parts['pass'] ?? null);
        $this->host = $this->prepareHost($parts['host'] ?? null);
        $this->port = $this->preparePort($parts['port'] ?? null);
        $this->path = $this->preparePath($parts['path'] ?? null);
        $this->query = $this->prepareQuery($parts['query'] ?? null);
        $this->fragment = $this->prepareFragment($parts['fragment'] ?? null);
    }


    /**
     * Alias withScheme()
     */
    public function setScheme(?string $scheme): UriInterface
    {
        return $this->withScheme($scheme);
    }

    /**
     * http or https
     */
    public function getScheme(): string
    {
        return (string)$this->scheme;
    }

    /**
     * New instance with specified scheme
     */
    public function withScheme($scheme): UriInterface
    {
        $scheme = $this->prepareScheme($scheme);

        if ($scheme === $this->scheme) {
            return $this;
        }

        $output = clone $this;
        $output->scheme = $scheme;

        return $output;
    }

    /**
     * Prepare scheme string
     */
    protected function prepareScheme(?string $scheme): ?string
    {
        $schema = strtolower((string)$scheme);
        $scheme = preg_replace('#:(//)?$#', '', $scheme);

        if (empty($scheme)) {
            return null;
        }

        if (!isset(self::SCHEMES[$scheme])) {
            throw df\Error::{'EInvalidArgument'}(
                'Scheme "'.$scheme.'" is unsupported'
            );
        }

        return $scheme;
    }



    /**
     * [user-info@]host[:port]
     */
    public function getAuthority(): string
    {
        if ($this->host === null) {
            return '';
        }

        $output = $this->host;

        if (!empty($user = $this->getUserInfo())) {
            $output = $user.'@'.$output;
        }

        if ($this->isCustomPort($this->scheme, $this->host, $this->port)) {
            $output .= ':'.$this->port;
        }

        return $output;
    }




    /**
     * Return new instance with username set
     */
    public function setUsername(?string $username): UriInterface
    {
        $username = $this->prepareUserInfo($username);

        if ($username === $this->username) {
            return $this;
        }

        $output = clone $this;
        $output->username = $username;

        return $output;
    }

    /**
     * Get prepared username if exists
     */
    public function getUsername(): ?string
    {
        if ($this->username === null) {
            return null;
        }

        return rawurldecode($this->username);
    }

    /**
     * Return new instance with password set
     */
    public function setPassword(?string $password): UriInterface
    {
        $password = $this->prepareUserInfo($password);

        if ($password === $this->password) {
            return $this;
        }

        $output = clone $this;
        $output->password = $password;

        return $output;
    }

    /**
     * Get prepared password if exists
     */
    public function getPassword(): ?string
    {
        if ($this->password !== null) {
            return null;
        }

        return rawurldecode($this->password);
    }


    /**
     * Alias withUserInfo()
     */
    public function setUserInfo(?string $username, ?string $password=null): UriInterface
    {
        return $this->withUserInfo($username, $password);
    }

    /**
     * user:pass
     */
    public function getUserInfo(): string
    {
        $output = (string)$this->username;

        if ($this->password !== null) {
            $output .= ':'.$this->password;
        }

        return $output;
    }

    /**
     * Return new instance with credentials set
     */
    public function withUserInfo($username, $password=null): UriInterface
    {
        $username = $this->prepareUserInfo($username);
        $password = $this->prepareUserInfo($password);

        if ($username === $this->username && $password === $this->password) {
            return $this;
        }

        $output = clone $this;
        $output->username = $username;
        $output->password = $password;

        return $output;
    }

    /**
     * Prepare credential string
     */
    protected static function prepareUserInfo(?string $info): ?string
    {
        if (empty($info)) {
            return null;
        }

        return preg_replace_callback(
            '/(?:[^%'.self::VALID_CHARACTERS.self::DELIMITERS.']+|%(?![A-Fa-f0-9]{2}))/u',
            function ($matches) {
                return rawurlencode($matches[0]);
            },
            $info
        );
    }



    /**
     * Alias setHost()
     */
    public function setHost(?string $host): UriInterface
    {
        return $this->withHost($host);
    }

    /**
     * Get host string
     */
    public function getHost(): string
    {
        return (string)$this->host;
    }

    /**
     * Return new instance with host set
     */
    public function withHost($host): UriInterface
    {
        $host = $this->prepareHost($host);

        if ($host === $this->host) {
            return $this;
        }

        $output = clone $this;
        $output->host = $host;

        return $this;
    }

    /**
     * Prepare host string
     */
    protected static function prepareHost(?string $host): ?string
    {
        if (empty($host)) {
            return null;
        }

        return strtolower($host);
    }



    /**
     * Alias withPort()
     */
    public function setPort(?int $port): UriInterface
    {
        return $this->withPort($port);
    }

    /**
     * Get the port if non-standard
     */
    public function getPort()
    {
        return $this->isCustomPort($this->scheme, $this->host, $this->port) ?
            $this->port : null;
    }

    /**
     * Return new instance with port set
     */
    public function withPort($port): UriInterface
    {
        $port = $this->preparePort($port);

        if ($port === $this->port) {
            return $this;
        }

        $output = clone $this;
        $output->port = $port;

        return $output;
    }

    /**
     * Prepare port number
     */
    protected static function preparePort($port): ?int
    {
        if (empty($port)) {
            return null;
        }

        if (!is_numeric($port)) {
            throw df\Error::EInvalidArgument(
                'Invalid port: '.$port
            );
        }

        $port = (int)$port;

        if ($port < 1 || $port > 65535) {
            throw df\Error::EInvalidArgument(
                'Invalid port: '.$port
            );
        }

        return $port;
    }

    /**
     * Check if port should be included
     */
    public static function isCustomPort(?string $scheme, ?string $host, ?int $port): bool
    {
        if ($scheme === null) {
            return !($host !== null && $port === null);
        }

        if ($host === null || $port === null) {
            return false;
        }

        return !isset(self::SCHEMES[$scheme]) ||
            $port !== self::SCHEMES[$scheme];
    }



    /**
     * Alias withPath()
     */
    public function setPath(?string $path): UriInterface
    {
        return $this->withPath($path);
    }

    /**
     * Get encoded path
     */
    public function getPath(): string
    {
        return (string)$this->path;
    }

    /**
     * Return new instance with path set
     */
    public function withPath($path): UriInterface
    {
        $path = $this->preparePath($path);

        if ($path === $this->path) {
            return $this;
        }

        $output = clone $this;
        $output->path = $path;

        return $output;
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
            throw df\Error::EInvalidArgument(
                'Invalid path, must not contain query string'
            );
        }

        if (strpos($path, '#') !== false) {
            throw df\Error::EInvalidArgument(
                'Invalid path, must not contain fragment'
            );
        }

        $path = preg_replace_callback(
            '/(?:[^'.self::VALID_CHARACTERS.')(:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/u',
            function ($matches) {
                return rawurlencode($matches[0]);
            },
            $path
        );

        if (substr($path, 0, 1) == '/') {
            $path = '/'.ltrim($path, '/');
        }

        return $path;
    }



    /**
     * Alias withQuery()
     */
    public function setQuery(?string $query): UriInterface
    {
        return $this->withQuery($query);
    }

    /**
     * Get original query string
     */
    public function getQuery(): string
    {
        return (string)$this->query;
    }

    /**
     * Return new instance with query set
     */
    public function withQuery($query)
    {
        $query = $this->prepareQuery($query);

        if ($query === $this->query) {
            return $this;
        }

        $output = clone $this;
        $output->query = $query;

        return $output;
    }

    /**
     * Prepare query string
     */
    protected static function prepareQuery(?string $query): ?string
    {
        if ($query === null || !strlen((string)$query)) {
            return null;
        }

        if (strpos($query, '#') !== false) {
            throw df\Error::EInvalidArgument(
                'Invalid query string - must not contain fragment'
            );
        }

        $query = ltrim($query, '?');
        $parts = explode('&', $query);

        foreach ($parts as $i => $part) {
            $vals = explode('=', $part, 2);
            $key = array_shift($vals);
            $value = array_shift($vals);

            if ($value === null) {
                $parts[$i] = self::prepareFragment($key);
            } else {
                $parts[$i] = self::prepareFragment($key).'='.self::prepareFragment($value);
            }
        }

        return implode('&', $parts);
    }


    /**
     * New instance with query set from tree
     */
    public function setQueryTree(?Tree $tree): UriInterface
    {
        $query = $tree->toDelimitedString();

        if (!strlen($query)) {
            $query = null;
        }

        if ($query === $this->query) {
            return $this;
        }

        $output = clone $this;
        $output->query = $query;

        return $output;
    }

    /**
     * Parse query string into tree object
     */
    public function getQueryTree(): Tree
    {
        return Tree::createFromDelimitedString($this->query);
    }


    /**
     * Alias withFragment()
     */
    public function setFragment(?string $fragment): UriInterface
    {
        return $this->withFragment($fragment);
    }

    /**
     * Original fragment
     */
    public function getFragment(): string
    {
        return (string)$this->fragment;
    }

    /**
     * New instance with fragment set
     */
    public function withFragment($fragment)
    {
        $fragment = $this->prepareFragment($fragment);

        if ($fragment === $this->fragment) {
            return $this;
        }

        $output = clone $this;
        $output->fragment = $fragment;
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
            '/(?:[^'.self::VALID_CHARACTERS.self::DELIMITERS.'%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/u',
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
        $output = '';

        if ($this->scheme !== null) {
            $output .= $this->scheme.':';
        }

        if (!empty($authority = $this->getAuthority())) {
            $output .= '//'.$authority;
        }

        if ($this->path !== null) {
            if (!strlen($this->path) || substr($this->path, 0, 1) !== '/') {
                $output .= '/';
            }

            $output .= $this->path;
        }

        if ($this->query !== null) {
            $output .= '?'.$this->query;
        }

        if ($this->fragment !== null) {
            $output .= '#'.$this->fragment;
        }

        return $output;
    }


    /**
     * Normalize for debug
     */
    public function __debugInfo(): array
    {
        return [
            '' => $this->__toString()
        ];
    }
}
