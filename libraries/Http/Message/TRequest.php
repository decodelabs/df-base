<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Http\Message;

use Df;
use Df\Http\Uri;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

trait TRequest
{
    use TMessage;

    protected $method = 'GET';
    protected $target = '/';
    protected $uri;

    private static $methods = [
        'OPTIONS', 'GET', 'HEAD', 'POST',
        'PUT', 'DELETE', 'TRACE', 'CONNECT'
    ];


    /**
     * Prepare object with provided data
     */
    protected function initRequest($uri, $method, $body, array $headers, string $protocol='1.1'): void
    {
        $method = strtoupper($method);

        if (!$this->isValidMethod($method)) {
            throw Df\Error::EInvalidArgument(
                'Invalid HTTP method: '.$method
            );
        }

        $this->method = $method;
        $this->uri = $this->prepareUri($uri);

        $this->initMessage($body, $headers, $protocol);

        if (!$this->hasHeader('host') && ($host = $this->uri->getHost())) {
            if ($port = $this->uri->getPort()) {
                $host .= ':'.$port;
            }

            $this->headerAliases['host'] = 'Host';
            $this->headers['Host'] = [$host];
        }
    }

    /**
     * Ensure URI object is available
     */
    protected function prepareUri($uri): UriInterface
    {
        if ($uri instanceof UriInterface) {
            return $uri;
        }

        return new Uri($uri);
    }


    /**
     * Alias withRequestTarget()
     */
    public function setRequestTarget(string $target): RequestInterface
    {
        return $this->withRequestTarget($target);
    }

    /**
     * Get stored request target or generate from uri
     */
    public function getRequestTarget(): string
    {
        if ($this->target !== null) {
            return $this->target;
        }

        $output = $this->uri->getPath();

        if (!empty($query = $this->uri->getQuery())) {
            $output .= '?'.$query;
        }

        if (!empty($output)) {
            $output = '/';
        }

        return $output;
    }

    /**
     * New instance with custom target
     */
    public function withRequestTarget($target): RequestInterface
    {
        if (preg_match('/\s/', $target)) {
            throw Df\Error::EInvalidArgument(
                'Request target must not contain spaces'
            );
        }

        $output = clone $this;
        $output->target = $target;

        return $output;
    }



    /**
     * Alias withMethod()
     */
    public function setMethod(string $method): RequestInterface
    {
        return $this->withMethod($method);
    }

    /**
     * Get method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * New instance with method
     */
    public function withMethod($method): RequestInterface
    {
        $method = strtoupper($method);

        if (!$this->isValidMethod($method)) {
            throw Df\Error::EInvalidArgument(
                'Invalid HTTP method: '.$method
            );
        }

        $output = clone $this;
        $output->method = $method;

        return $output;
    }

    /**
     * Check method
     */
    public static function isValidMethod(?string $method): bool
    {
        return in_array($method, static::$methods);
    }

    /**
     * Get list of available HTTP methods
     */
    public static function getValidMethods(): array
    {
        return static::$methods;
    }



    /**
     * Alias withUri()
     */
    public function setUri(UriInterface $uri, bool $preserveHost=false): RequestInterface
    {
        return $this->withUri($uri, $preserveHost);
    }

    /**
     * Get uri instance
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * New instance with URI replaced
     */
    public function withUri(UriInterface $uri, $preserveHost=false): RequestInterface
    {
        $output = clone $this;
        $output->uri = $uri;
        $host = $uri->getHost();

        if (($preserveHost && $this->hasHeader('Host')) || empty($host)) {
            return $output;
        }

        if ($port = $uri->getPort()) {
            $host .= ':'.$port;
        }

        $output->headerAliases['host'] = 'Host';

        foreach (array_keys($output->headers) as $name) {
            if (strtolower($name) === 'host') {
                unset($output->headers[$name]);
            }
        }

        $output->headers['Host'] = [$host];
        return $output;
    }
}
