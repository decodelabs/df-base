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

use Psr\Http\Message\UriInterface;

class Redirect extends Stream
{
    /**
     * Init with text stream and content type headers set
     */
    public function __construct(UriInterface $uri, int $status=200, array $headers=[])
    {
        parent::__construct(
            MessageStream::createFromString('', 'wb+'),
            $status,
            $this->injectDefaultHeaders([
                'location' => [(string)$uri]
            ], $headers)
        );
    }
}
