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

        $this->sendResponse($response);
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
        return new http\response\Html('<h1>Hello world</h1>');
    }

    /**
     * Ensure the response is sent - generally just a wrapper
     */
    public function sendResponse(ResponseInterface $response): void
    {
        if (headers_sent()) {
            throw df\Error::ERuntime('Cannot send request, headers already sent');
        }

        $status = $response->getStatusCode();
        $phrase = $response->getReasonPhrase();

        // Send headers
        static $mergable = ['Set-Cookie'];

        foreach ($response->getHeaders() as $header => $values) {
            $name = str_replace('-', ' ', $header);
            $name = ucwords($name);
            $name = str_replace(' ', '-', $name);
            $merge = in_array($name, $mergable);

            foreach ($values as $value) {
                header($name.': '.$value, $merge, $status);
            }
        }

        // Send status
        $header = 'HTTP/'.$response->getProtocolVersion();
        $header .= ' '.(string)$status;

        if (!empty($phrase)) {
            $header .= ' '.$phrase;
        }

        header($header, true, $status);


        // Send body
        // TODO: check status needs body
        echo $response->getBody();
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
