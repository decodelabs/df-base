<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\http\response;

use df;
use df\http;

use df\http\message\Stream as MessageStream;

class Text extends Stream
{
    /**
     * Init with text stream and content type headers set
     */
    public function __construct(string $text, int $status=200, array $headers=[])
    {
        parent::__construct(
            MessageStream::createFromString($text, 'wb+'),
            $status,
            $this->injectDefaultHeaders([
                'content-type' => 'text/plain; charset=utf-8',
                //'content-length' => strlen($text)
            ], $headers)
        );
    }
}
