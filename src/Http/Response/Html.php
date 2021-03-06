<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Http\Response;

use Df\Http\Message\Stream as MessageStream;

class Html extends Stream
{
    /**
     * Init with text stream and content type headers set
     */
    public function __construct(string $html, int $status=200, array $headers=[])
    {
        parent::__construct(
            MessageStream::fromString($html, 'wb+'),
            $status,
            $this->injectDefaultHeaders([
                'content-type' => 'text/html; charset=utf-8',
                //'content-length' => strlen($html)
            ], $headers)
        );
    }
}
