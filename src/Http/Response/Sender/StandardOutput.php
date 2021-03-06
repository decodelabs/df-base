<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Http\Response\Sender;

use Df;

use Df\Http\Response\Sender;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use DecodeLabs\Exceptional;

class StandardOutput implements Sender
{
    const MERGE_HEADERS = [
        'Set-Cookie'
    ];


    protected $sendfile = 'X-Sendfile';
    protected $manualChunk = true;
    protected $sendDebug = true;


    /**
     * Define sendfile header
     */
    public function setSendfileHeader(?string $sendfile): StandardOutput
    {
        $this->sendfile = $sendfile;
        return $this;
    }

    /**
     * Get sendfile header
     */
    public function getSendfileHeader(): ?string
    {
        return $this->sendfile;
    }



    /**
     * Set manual chunking
     */
    public function setManualChunk(bool $chunk): StandardOutput
    {
        $this->manualChunk = $chunk;
        return $this;
    }

    /**
     * Will this send chunk manually?
     */
    public function shouldManualChunk(): bool
    {
        return $this->manualChunk;
    }


    /**
     * Send debug info in headers
     */
    public function setSendDebug(bool $send): StandardOutput
    {
        $this->sendDebug = $send;
        return $this;
    }

    /**
     * Should we send debug info?
     */
    public function shouldSendDebug(): bool
    {
        return $this->sendDebug;
    }



    /**
     * Ensure the response is sent - generally just a wrapper
     */
    public function sendResponse(ServerRequestInterface $request, ResponseInterface $response): void
    {
        if (headers_sent()) {
            throw Exceptional::Runtime(
                'Cannot send response, headers already sent'
            );
        }

        $status = $response->getStatusCode();
        $phrase = $response->getReasonPhrase();
        $stream = $response->getBody();
        $sendData = true;

        // Send headers
        foreach ($response->getHeaders() as $header => $values) {
            $name = str_replace('-', ' ', $header);
            $name = ucwords($name);
            $name = str_replace(' ', '-', $name);
            $merge = in_array($name, static::MERGE_HEADERS);

            if ($name === $this->sendfile) {
                $sendData = false;
            }

            foreach ($values as $value) {
                header($name.': '.$value, $merge, $status);
            }
        }


        // Hand off using x-sendfile
        if ($sendData &&
            $this->sendfile !== null &&
            $stream->getMetadata('wrapper_type') === 'plainfile' &&
            ($filePath = $stream->getMetadata('uri'))) {
            header($this->sendfile.': '.$filePath, true, $status);
            $sendData = false;
        }


        // Debug time
        if ($this->sendDebug) {
            header('X-Request-Time: '.number_format((microtime(true) - Df\START) * 1000, 2).' ms', false, $status);
        }


        // Send status
        $header = 'HTTP/'.$response->getProtocolVersion();
        $header .= ' '.(string)$status;

        if (!empty($phrase)) {
            $header .= ' '.$phrase;
        }

        header($header, true, $status);



        // Check request requiring data
        if ($request->getMethod() === 'HEAD' ||
            $status === 304) {
            $sendData = false;
        }


        // Send body if we need to
        if ($sendData) {
            $this->sendBody($response);
        }
    }


    /**
     * Send body data
     */
    protected function sendBody(ResponseInterface $response): void
    {
        $stream = $response->getBody();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        flush();

        $isChunked = $this->manualChunk ?
            $response->getHeaderLine('transfer-encoding') == 'chunked' :
            false;

        while (!$stream->eof()) {
            $chunk = $stream->read(4096);

            if ($isChunked) {
                echo dechex(strlen($chunk))."\r\n";
                echo $chunk."\r\n";
                flush();
            } else {
                echo $chunk;
                flush();
            }
        }

        // Send end chunk
        if ($isChunked) {
            echo "0\r\n\r\n";
            flush();
        }
    }
}
