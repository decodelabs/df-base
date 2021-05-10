<?php

/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace Df\Apex\Http\Front;

use Df;
use Df\Arch\Router;

class AppRouter extends Router
{
    public function setup(): void
    {
        $this->node('/', 'index');


        /*
        $this->get('{path?/}', 'test', function ($path, Df\Arch\Context $context) {
            return 'Everything else: '.$context->request;
        });
        */


        return;

        // http://mydomain.com/
        // view://~front/home/index.twig
        $this->view('/', 'home/index.twig')
            ->throttle(60);


        // http://mydomain.com/assets/styles.css
        // asset://~front/styles.css
        $this->asset('assets/styles.css', 'profiles/style.css');



        $this->group('myMiddleware:hello', function () {
            $this->addMiddlewares('throttle:60', 'another:stuff,things');

            $this->view('hello', 'home/hello.twig');
            $this->node('some-other-path/{stuff}', 'mynode');
        });


        $this->mount('profile', function () {
            $this->view('/', 'account/info.twig');
            $this->node('settings', 'account/settings');
        });
    }
}
