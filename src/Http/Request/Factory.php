<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Http\Request;

use Df;
use Df\Http\Uri;
use Df\Http\Message\UploadedFile;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

class Factory
{
    /**
     * Generate ServerRequest from global environment variables
     */
    public function fromEnvironment(
        array $server=null,
        array $query=null,
        array $body=null,
        array $cookies=null,
        array $files=null
    ): ServerRequestInterface {
        $server = $this->prepareServerData($server ?? $_SERVER);
        $files = $this->prepareFiles($files ?? $_FILES);
        $headers = $this->extractHeaders($server);
        $uri = $this->extractUri($server, $headers);

        if ($cookies === null && array_key_exists('cookie', $headers)) {
            $cookies = $this->parseCookies($headers['cookie']);
        }

        if ($query === null) {
            parse_str($uri->getQuery(), $query);
        }

        return new ServerRequest(
            $server,
            $files,
            $uri,
            $server['REQUEST_METHOD'] ?? 'GET',
            'php://input',
            $headers,
            $cookies ?? $_COOKIE,
            $query ?? $_GET,
            $body ?? $_POST,
            $this->extractProtocol($server)
        );
    }

    /**
     * Normalize $_SERVER or equivalent
     */
    public function prepareServerData(array $server): array
    {
        if (function_exists('apache_request_headers')) {
            $apache = apache_request_headers();
            $apache = array_change_key_case($apache, CASE_LOWER);

            if (isset($apache['authorization'])) {
                $server['HTTP_AUTHORIZATION'] = $apache['authorization'];
            }
        }

        return $server;
    }

    /**
     * Normalize $_FILES or equivalent
     */
    public function prepareFiles(array $files): array
    {
        $output = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $output[$key] = $value;
            } elseif (is_array($value) && isset($value['tmp_name'])) {
                $output[$key] = $this->createUploadedFile($value);
            } elseif (is_array($value)) {
                $output[$key] = $this->prepareFiles($value);
            } else {
                throw Df\Error::EInvalidArgument('Invalid $_FILES array', null, $files);
            }
        }

        return $output;
    }

    /**
     * Prepare header list from $_SERVER
     */
    public function extractHeaders(array $server): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'REDIRECT_') === 0) {
                $key = substr($key, 9);

                if (array_key_exists($key, $_SERVER)) {
                    continue;
                }
            }

            if ($value && strpos($key, 'HTTP_') === 0) {
                $name = strtr(strtolower(substr($key, 5)), '_', '-');
                $headers[$name] = $value;
                continue;
            }

            if ($value && strpos($key, 'CONTENT_') === 0) {
                $name = 'content-' . strtolower(substr($key, 8));
                $headers[$name] = $value;
                continue;
            }
        }

        return $headers;
    }

    /**
     * Convert cookie header to array
     */
    public function parseCookies(string $string): array
    {
        preg_match_all('(
            (?:^\\n?[ \t]*|;[ ])
            (?P<name>[!#$%&\'*+-.0-9A-Z^_`a-z|~]+)
            =
            (?P<DQUOTE>"?)
                (?P<value>[\x21\x23-\x2b\x2d-\x3a\x3c-\x5b\x5d-\x7e]*)
            (?P=DQUOTE)
            (?=\\n?[ \t]*$|;[ ])
        )x', $string, $matches, PREG_SET_ORDER);

        $cookies = [];

        foreach ($matches as $match) {
            $cookies[$match['name']] = urldecode($match['value']);
        }

        return $cookies;
    }


    /**
     * Prepare URI from env
     */
    public function extractUri(array $server, array $headers): UriInterface
    {
        [$host, $port] = $this->extractHostAndPort($server, $headers);
        $relative = $this->extractRelative($server);
        $parts = explode('#', $relative, 2);
        $relative = array_shift($parts);
        $fragment = array_shift($parts);
        $parts = explode('?', $relative, 2);
        $path = array_shift($parts);
        $query = array_shift($parts);

        return Uri::create(
            $this->extractScheme($server, $headers),
            null,
            null,
            $host,
            $port,
            $path,
            $query,
            $fragment
        );
    }


    /**
     * Extract scheme from env
     */
    public function extractScheme(array $server, array $headers): string
    {
        $output = 'http';
        $headers = array_change_key_case($headers, CASE_LOWER);

        if (($server['HTTPS'] ?? 'off') !== 'off' ||
            ($headers['x-forwarded-proto'] ?? null) === 'https') {
            $output = 'https';
        }

        return $output;
    }

    /**
     * Extract host from env
     */
    public function extractHostAndPort(array $server, array $headers): array
    {
        if (isset($headers['host']) || isset($headers['x-original-host'])) {
            $host = $headers['host'] ?? $headers['x-original-host'];
            $port = null;

            if (preg_match('|\:(\d+)$|', $host, $matches)) {
                $host = substr($host, 0, -(strlen($matches[1]) + 1));
                $port = (int)$matches[1];
            }
        } elseif (!isset($server['SERVER_NAME']) && isset($server['SERVER_ADDR'])) {
            $host = '['.$server['SERVER_ADDR'].']';
            $port = null;
        } else {
            $host = $server['SERVER_NAME'] ?? null;
            $port = $server['SERVER_PORT'] ?? null;
        }

        return [$host, $port];
    }

    /**
     * Extract path, query and fragment from env
     */
    public function extractRelative(array $server): string
    {
        $iisRewrite = $server['IIS_WasUrlRewritten'] ?? null;
        $unencoded = $server['UNENCODED_URL'] ?? null;

        if ($iisRewrite === '1' && $unencoded !== null) {
            return $unencoded;
        }

        $output =
            $server['HTTP_X_REWRITE_URL'] ??
            $server['HTTP_X_ORIGINAL_URL'] ??
            $server['REQUEST_URI'] ??
            null;

        if ($output !== null) {
            return preg_replace('#^[^/:]+://[^/]+#', '', $output);
        }

        return $server['ORIG_PATH_INFO'] ?? '/';
    }


    /**
     * Extract protocol from env
     */
    public function extractProtocol(array $server): string
    {
        if (null === ($output = ($server['SERVER_PROTOCOL'] ?? null))) {
            return null;
        }

        if (!preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $output, $matches)) {
            throw Df\Error::EUnexpectedValue(
                'Unrecognized HTTP protocal version: '.$output
            );
        }

        return $matches['version'];
    }


    /**
     * Create uploadFile object
     */
    public function createUploadedFile(array $file): UploadedFileInterface
    {
        if (is_array($file['tmp_name'])) {
            return $this->normalizeNestedFiles($file);
        }

        return new UploadedFile(
            $file['tmp_name'],
            $file['size'],
            $file['error'],
            $file['name'],
            $file['type']
        );
    }

    /**
     * Normalize nested files
     */
    protected function normalizeNestedFiles(array $files): array
    {
        $output = [];

        foreach (array_keys($files['tmp_name']) as $key) {
            $output[$key] = $this->createUploadedFile([
                $files['tmp_name'][$key],
                $files['size'][$key],
                $files['error'][$key],
                $files['name'][$key],
                $files['type'][$key]
            ]);
        }

        return $output;
    }
}
