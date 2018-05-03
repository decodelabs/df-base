<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\http\response;

use df;
use df\http\message\Stream as MessageStream;

class Json extends Stream
{
    /**
     * Init with data
     */
    public function __construct($data, int $status=200, array $headers=[])
    {
        $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

        parent::__construct(
            MessageStream::createFromString($json, 'wb+'),
            $status,
            $this->injectDefaultHeaders([
                'content-type' => 'application/json'
            ], $headers)
        );
    }
}