<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Http\Response;

use Df\Http\Message\Stream as MessageStream;

class Json extends Stream
{
    /**
     * Init with data
     */
    public function __construct($data, int $status=200, array $headers=[])
    {
        $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw Glitch::EUnexpectedValue('Unable to encode json for stream', null, $data);
        }

        parent::__construct(
            MessageStream::fromString($json, 'wb+'),
            $status,
            $this->injectDefaultHeaders([
                'content-type' => 'application/json'
            ], $headers)
        );
    }
}
