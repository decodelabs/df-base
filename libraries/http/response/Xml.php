<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\http\response;

use df;
use df\http;

class Xml extends Stream
{
    /**
     * Init with text stream and content type headers set
     */
    public function __construct(string $xml, int $status=200, array $headers=[])
    {
        parent::__construct(
            http\body\Stream::createFromString($xml, 'wb+'),
            $status,
            $this->injectDefaultHeaders([
                'content-type' => 'application/xml; charset=utf-8',
                //'content-length' => strlen($xml)
            ], $headers)
        );
    }
}
