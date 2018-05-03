<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\http\request;

use df;
use df\http\message\TRequest;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

class ServerRequest implements ServerRequestInterface
{
    use TRequest;

    protected $query = [];
    protected $attributes = [];
    protected $cookies = [];
    protected $files = [];
    protected $server = [];
    protected $bodyData;

    /**
     * Init
     */
    public function __construct(
        array $server=[],
        array $files=[],
        $uri=null,
        $method=null,
        $body='php://input',
        array $headers=[],
        array $cookies=[],
        array $query=[],
        $bodyData=null,
        $protocol='1.1'
    ) {
        $this->initRequest($uri, $method, $body, $headers, $protocol);
        $this->server = $server;
        $this->files = $files;
        $this->cookies = $cookies;
        $this->query = $query;
        $this->bodyData = $bodyData;
    }



    /**
     * Get $_SERVER equiv
     */
    public function getServerParams(): array
    {
        return $this->server;
    }

    /**
     * Get single server param
     */
    public function getServerParam(string $key): ?string
    {
        if (!isset($this->server[$key])) {
            return null;
        }

        return (string)$this->server[$key];
    }

    /**
     * Is $key in $server?
     */
    public function hasServerParam(string $key): bool
    {
        return isset($this->server[$key]);
    }



    /**
     * Alias withCookieParams()
     */
    public function setCookieParams(array $cookies): ServerRequestInterface
    {
        return $this->withCookieParams($cookies);
    }

    /**
     * Get injected cookies
     */
    public function getCookieParams(): array
    {
        return $this->cookies;
    }

    /**
     * Get single cookie value
     */
    public function getCookieParam(string $key): ?string
    {
        if (!isset($this->cookies[$key])) {
            return null;
        }

        return (string)$this->cookies[$key];
    }

    /**
     * Is $cookie set?
     */
    public function hasCookieParam(string $key): bool
    {
        return isset($this->cookies[$key]);
    }

    /**
     * New instance with cookies set
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $output = clone $this;
        $output->cookies = $cookies;

        return $output;
    }


    /**
     * Alias withQueryParams()
     */
    public function setQueryParams(array $query): ServerRequestInterface
    {
        return $this->withQueryParams($query);
    }

    /**
     * Get injected query params
     */
    public function getQueryParams(): array
    {
        return $this->query;
    }

    /**
     * Get query param
     */
    public function getQueryParam(string $key): ?string
    {
        if (!isset($this->query[$key])) {
            return null;
        }

        return (string)$this->query[$key];
    }

    /**
     * Is key in the query?
     */
    public function hasQueryParam(string $key): bool
    {
        return isset($this->query[$key]);
    }

    /**
     * New instance with query params set
     */
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $output = clone $this;
        $output->query = $query;

        return $output;
    }


    /**
     * Get injected file list
     */
    public function getUploadedFiles(): array
    {
        return $this->files;
    }

    /**
     * New instance with files added
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $this->prepareUploadedFiles($uploadedFiles);

        $output = clone $this;
        $output->files = $uploadedFiles;

        return $output;
    }

    /**
     * Prepare file array
     */
    protected function prepareUploadedFiles(array $files): array
    {
        foreach ($files as $file) {
            if (is_array($file)) {
                $this->prepareUploadedFiles($file);
                continue;
            }

            if (!$file instanceof UploadedFileInterface) {
                throw df\Error::EInvalidArgument(
                    'Invalid uploaded file array - files must be instances of UploadedFileInterface'
                );
            }
        }

        return $files;
    }


    /**
     * Get structured body data
     */
    public function getParsedBody()
    {
        return $this->bodyData;
    }

    /**
     * New instance with structured body data added
     */
    public function withParsedBody($data): ServerRequestInterface
    {
        $output = clone $this;
        $output->bodyData = $data;

        return $output;
    }



    /**
     * Set attribute list
     */
    public function setAttributes(array $attributes): ServerRequestInterface
    {
        $output = clone $this;
        $output->attributes = $attributes;

        return $output;
    }

    /**
     * Get attribute list
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }


    /**
     * Alias withAttribute()
     */
    public function setAttribute(string $name, $value): ServerRequestInterface
    {
        return $this->withAttribute($name, $value);
    }

    /**
     * Get attribute value
     */
    public function getAttribute($name, $default=null)
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$name];
    }

    /**
     * Does attribute exist?
     */
    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * New instance with attribute set
     */
    public function withAttribute($name, $value): ServerRequestInterface
    {
        $output = clone $this;
        $output->attributes[$name] = $value;

        return $output;
    }

    /**
     * Alias withoutAttribute()
     */
    public function removeAttribute(string $name): ServerRequestInterface
    {
        return $this->withoutAttribute($name);
    }

    /**
     * New instance with attribute removed
     */
    public function withoutAttribute($name): ServerRequestInterface
    {
        $output = clone $this;
        unset($output->attributes[$name]);

        return $output;
    }
}
