<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip;

use Df;
use Df\Core\IApp;
use Df\Clip\IShell;
use Df\Clip\Command\IRequest;
use Df\Clip\Command\Styles;

use Df\Plug\TContext;
use Df\Clip\IContext;

use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

use Df\Clip\Input\Question;
use Df\Clip\Input\Password;
use Df\Clip\Input\Confirmation;

use Df\Clip\Widget\Spinner;
use Df\Clip\Widget\ProgressBar;

class Context implements IContext
{
    use TContext;
    use LoggerTrait;

    protected $request;
    protected $shell;
    protected $styles;

    /**
     * Init with http request and location uri
     */
    public function __construct(IApp $app, IRequest $request, IShell $shell, Styles $styles=null)
    {
        $this->app = $app;
        $this->request = $request;
        $this->shell = $shell;
        $this->styles = $styles ?? new Styles($shell->canColor());
    }

    /**
     * Clone children
     */
    public function __clone()
    {
        $this->request = clone $this->request;
        $this->styles = clone $this->styles;
    }

    /**
     * Pass calls through to shell
     */
    public function __call(string $method, array $args)
    {
        return $this->shell->{$method}(...$args);
    }



    /**
     * Render output from a task
     */
    public function render($message, ?string $modifier=null): void
    {
        if (is_callable($message) && is_object($message)) {
            $this->render($message(), $modifier);
            return;
        }

        if ($message instanceof \Generator) {
            foreach ($message as $modifier => $value) {
                if (is_int($modifier)) {
                    $modifier = null;
                }

                $this->render($value, $modifier);
            }

            return;
        }

        $message = $this->styles->modify((string)$message, $modifier, $channel);

        if ($channel === 'error') {
            $this->shell->writeError($message);
        } else {
            $this->shell->write($message);
        }
    }


    /**
     * Render log
     */
    public function success($message, array $context=[])
    {
        $this->log('success', $message, $context);
    }

    public function log($level, $message, array $context=[])
    {
        $message = $this->interpolate((string)$message, $context);
        $message = $this->styles->apply($level, $message, $channel);

        if ($channel === 'error') {
            $this->shell->writeErrorLine($message);
        } else {
            $this->shell->writeLine($message);
        }
    }

    private function interpolate(string $message, array $context=[]): string
    {
        $replace = [];

        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{'.$key.'}'] = $val;
            }
        }

        return strtr($message, $replace);
    }




    /**
     * Clear screen and move cursor to top
     */
    public function clearScreen(): IContext
    {
        $this->shell->writeLine("\e[H\e[2J");
        return $this;
    }

    /**
     * Write a tab to the console
     */
    public function tab(int $count=1): IContext
    {
        $this->shell->write(str_repeat("\t", $count));
        return $this;
    }



    /**
     * Ask a question
     */
    public function ask(string $message, string $default=null): Question
    {
        return new Question($this, $message, $default);
    }

    /**
     * Ask for password
     */
    public function askPassword(string $message): Password
    {
        return new Password($this, $message);
    }

    /**
     * Ask for confirmation
     */
    public function confirm(string $message, bool $default=null): Confirmation
    {
        return new Confirmation($this, $message, $default);
    }



    /**
     * Show progress indicator
     */
    public function spinner(string $style=null): Spinner
    {
        return new Spinner($this, $style);
    }

    /**
     * Show progress bar
     */
    public function progressBar(float $min=0, float $max=100): ProgressBar
    {
        return new ProgressBar($this, $min, $max);
    }
}
