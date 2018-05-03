<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\http;

use df;
use df\http\pipeline\IDispatcher;
use df\http\response\ISender;
use df\core\IApp;
use df\core\kernel\IHttp;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Kernel implements IHttp
{
    protected $app;
    protected $dispatcher;
    protected $sender;

    /**
     * Setup with ref to $app
     */
    public function __construct(IApp $app, IDispatcher $dispatcher, ISender $sender)
    {
        $this->app = $app;
        $this->dispatcher = $dispatcher;
        $this->sender = $sender;
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
        $this->dispatcher->queueList($this->app->getGlobalMiddleware());
        return $this->dispatcher->handle($request);
    }

    /**
     * Ensure the response is sent - generally just a wrapper
     */
    public function sendResponse(ServerRequestInterface $request, ResponseInterface $response): void
    {
        $this->sender->sendResponse($request, $response);
    }

    /**
     * Close down any middleware and the app
     */
    public function terminate(ServerRequestInterface $request, ResponseInterface $response): void
    {
        $this->dispatcher->terminate($request, $response);
        $this->app->terminate();
        exit;
    }
}
