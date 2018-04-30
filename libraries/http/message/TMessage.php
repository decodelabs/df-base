<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\http\message;

use df;
use df\http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

trait TMessage
{
    protected $protocol = '1.1';
    protected $headers = [];
    protected $headerAliases = [];
    protected $body;


    /**
     * Initialise message data
     */
    protected function initMessage($uri, $body, array $headers, string $protocol='1.1'): void
    {
        $this->protocol = $this->prepareProtocolVersion($protocol);
        $this->body = $this->prepareStream($body);
        $this->uri = $this->prepareUri($uri);
        $this->setHeaders($headers);

        if (!$this->hasHeader('host') && ($host = $this->uri->getHost())) {
            if ($port = $this->uri->getPort()) {
                $host .= ':'.$port;
            }

            $this->headerAliases['host'] = 'Host';
            $this->headers['Host'] = [$host];
        }
    }


    /**
     * Alias withProtocolVersion()
     */
    public function setProtocolVersion(string $version): MessageInterface
    {
        return $this->withProtocolVersion($version);
    }

    /**
     * Get HTTP version 1.0 or 1.1
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * Return new version with version set
     */
    public function withProtocolVersion($version): MessageInterface
    {
        $output = clone $this;
        $output->protocol = $this->prepareProtocolVersion($version);

        return $output;
    }

    /**
     * Prepare protocol version
     */
    protected function prepareProtocolVersion(?string $version): string
    {
        if (!preg_match('#^(1\.[01]|2)$#', (string)$version)) {
            throw df\Error::EInvalidArgument(
                'Invalid HTTP protocol version: '.$version,
                null,
                $version
            );
        }

        return $version;
    }


    /**
     * Get raw header array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Has this header been set?
     */
    public function hasHeader($name): bool
    {
        return isset($this->headerAliases[strtolower($name)]);
    }

    /**
     * Get header value stack
     */
    public function getHeader($name): array
    {
        if (!$this->hasHeader($name)) {
            return [];
        }

        $name = $this->headerAliases[strtolower($name)];
        return $this->headers[$name];
    }

    /**
     * Get comma separate list of headers by name
     */
    public function getHeaderLine($name): string
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * Alias withHeader()
     */
    public function setHeader(string $name, $value): MessageInterface
    {
        return $this->withHeader($name, $value);
    }

    /**
     * Return new instance with header set
     */
    public function withHeader($name, $value): MessageInterface
    {
        if (!$this->isHeaderNameValid($name)) {
            throw df\Error::EInvalidArgument(
                'Invalid header name: '.$name
            );
        }

        $output = clone $this;
        $output->headers[$name] = $this->prepareHeader($value);
        $output->headerAliases[strtolower($name)] = $name;

        return $output;
    }

    /**
     * Alias withAddedHeader()
     */
    public function addHeader(string $name, $value): MessageInterface
    {
        return $this->withAddedHeader($name, $value);
    }

    /**
     * Merge $value with current value stack
     */
    public function withAddedHeader($name, $value): MessageInterface
    {
        if (!$this->isHeaderNameValid($name)) {
            throw df\Error::EInvalidArgument(
                'Invalid header name: '.$name
            );
        }

        $output = clone $this;
        $output->headers[$name] = array_merge($output->headers[$name] ?? [], $this->prepareHeader($value));
        $output->headerAliases[strtolower($name)] = $name;

        return $output;
    }

    /**
     * Alias withoutHeader
     */
    public function removeHeader(string $name): MessageInterface
    {
        return $this->withoutHeader($name);
    }

    /**
     * Remove header
     */
    public function withoutHeader($name): MessageInterface
    {
        $output = clone $this;
        unset($output->headers[$name], $output->headerAliases[strtolower($name)]);

        return $output;
    }

    /**
     * Apply list of headers
     */
    protected function setHeaders(array $input): void
    {
        $headers = $aliases = [];

        foreach ($input as $name => $value) {
            if (!$this->isHeaderNameValid($name)) {
                throw df\Error::EInvalidArgument(
                    'Invalid header name: '.$name
                );
            }

            $headers[$name] = $this->prepareHeader($value);
            $aliases[strtolower($name)] = $name;
        }

        $this->headers = $headers;
        $this->headerAliases = $aliases;
    }

    /**
     * Prepare a header value
     */
    protected function prepareHeader($value): array
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        return array_map(function ($value) {
            if (!$this->isHeaderValueValid($value)) {
                throw df\Error::EInvalidArgument(
                    'Invalid header value',
                    null,
                    $value
                );
            }

            return (string)$value;
        }, $value);
    }

    /**
     * Is a header key valid?
     */
    public static function isHeaderNameValid(string $key): bool
    {
        return (bool)preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $key);
    }

    /**
     * Is a header valid?
     */
    public static function isHeaderValueValid($value): bool
    {
        if (!is_scalar($value) || $value === null) {
            return false;
        }

        if (preg_match("#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#", $value)) {
            return false;
        }

        if (preg_match('/[^\x09\x0a\x0d\x20-\x7E\x80-\xFE]/', $value)) {
            return false;
        }

        return true;
    }


    /**
     * Alias withBody()
     */
    public function setBody(StreamInterface $body): MessageInterface
    {
        return $this->withBody($body);
    }

    /**
     * Get active body stream object
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * Replace body stream
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $output = clone $this;
        $output->body = $body;

        return $output;
    }

    /**
     * Ensure stream object is available
     */
    protected function prepareStream($stream, string $mode='r'): StreamInterface
    {
        if ($stream instanceof StreamInterface) {
            return $stream;
        }

        return new http\body\Stream($stream, $mode);
    }
}
