<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Http\Response;

use Df\Http\Message\TMessage;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

use DecodeLabs\Glitch;

class Stream implements ResponseInterface
{
    use TMessage;

    const CODES = [
        // Info code
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',

        // Success codes
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',

        // Redirect codes
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated to 306 => '(Unused)'
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',

        // Client codes
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',

        // Server codes
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error'
    ];

    protected $status;
    protected $phrase;

    /**
     * Initiate standard response using stream
     */
    public function __construct($body='php://memory', int $status=200, array $headers=[])
    {
        $this->status = $this->prepareStatusCode($status);
        $this->initMessage($body, $headers);
    }

    /**
     * Alias withStatus()
     */
    public function setStatusCode(int $code, ?string $phrase=null): ResponseInterface
    {
        return $this->withStatus($code, $phrase);
    }

    /**
     * Get current status code
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * New instance with status code set
     */
    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        if (empty($reasonPhrase)) {
            $reasonPhrase = null;
        }

        $output = clone $this;
        $output->status = $this->prepareStatusCode((int)$code);
        $output->reasonPhrase = $reasonPhrase;

        return $output;
    }

    /**
     * Get HTTP status message
     */
    public function getReasonPhrase(): string
    {
        return $this->phrase ?? static::CODES[$this->status];
    }

    /**
     * Ensure code is valid
     */
    protected function prepareStatusCode(int $code): int
    {
        if (!isset(static::CODES[$code])) {
            throw Glitch::EInvalidArgument(
                'Invalid HTTP status code: '.$code
            );
        }

        return $code;
    }
}
