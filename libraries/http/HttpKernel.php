<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\http;

use df;
use df\http;
use df\core\IApp;
use df\core\kernel\IHttp;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpKernel implements IHttp
{
    protected $app;

    /**
     * Setup with ref to $app
     */
    public function __construct(IApp $app)
    {
        $this->app = $app;
    }

    /**
     * Full stack wrapper around default behaviour
     */
    public function run(): void
    {
        $request = $this->prepareServerRequest();
        $response = $this->handle($request);

        $this->sendResponse($request, $response);
        $this->terminate($request, $response);
    }

    /**
     * Generate the server request to work from
     */
    public function prepareServerRequest(): ServerRequestInterface
    {
        return $this->app[ServerRequestInterface::class];
    }

    /**
     * Handle the request and return a response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new http\response\Stream(
            new http\body\Generator(function () {
                for ($i = 0; $i < 10000; $i++) {
                    yield 'This is line '.$i."\n";
                }
            }),
            200,
            [
                'content-type' => 'text/plain',
                'transfer-encoding' => 'chunked'
            ]
        );
    }

    /**
     * Ensure the response is sent - generally just a wrapper
     */
    public function sendResponse(ServerRequestInterface $request, ResponseInterface $response): void
    {
        if (headers_sent()) {
            throw df\Error::ERuntime('Cannot send response, headers already sent');
        }

        static $sendFile = 'X-Sendfile';
        static $chunkManual = true;

        $status = $response->getStatusCode();
        $phrase = $response->getReasonPhrase();
        $stream = $response->getBody();
        $sendData = true;

        // Send headers
        static $mergable = ['Set-Cookie'];

        foreach ($response->getHeaders() as $header => $values) {
            $name = str_replace('-', ' ', $header);
            $name = ucwords($name);
            $name = str_replace(' ', '-', $name);
            $merge = in_array($name, $mergable);

            if ($name === $sendFile) {
                $sendData = false;
            }

            foreach ($values as $value) {
                header($name.': '.$value, $merge, $status);
            }
        }


        // Hand off using x-sendfile
        if ($sendData &&
            $sendFile !== null &&
            $stream->getMetadata('wrapper_type') === 'plainfile' &&
            ($filePath = $stream->getMetadata('uri'))) {
            header($sendFile.': '.$filePath);
            $sendData = false;
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

        if (!$sendData) {
            return;
        }


        // Send body
        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        flush();
        $isChunked = $chunkManual ? $response->getHeaderLine('transfer-encoding') == 'chunked' : false;

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

    /**
     * Close down any middleware and the app
     */
    public function terminate(ServerRequestInterface $request, ResponseInterface $response): void
    {
        // TODO: terminate middleware

        $this->app->terminate();
    }
}
