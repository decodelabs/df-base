<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip\Input;

use Df;
use Df\Clip\IInput;
use Df\Clip\IContext;

class Password implements IInput
{
    protected $message;
    protected $context;

    /**
     * Init with message
     */
    public function __construct(IContext $context, string $message)
    {
        $this->context = $context;
        $this->message = $message;
    }

    /**
     * Get body of the question
     */
    public function getMessage(): string
    {
        return $this->message;
    }


    /**
     * Ask the question
     */
    public function prompt(): ?string
    {
        $done = false;

        while (!$done) {
            $this->renderQuestion();

            system('stty -echo');
            $password = $this->context->shell->readLine();
            system('stty echo');

            if ($this->validate($password)) {
                $done = true;
                $this->context->render('••••••••', 'red');
            }
        }

        return $password;
    }

    /**
     * Render question
     */
    protected function renderQuestion(): void
    {
        $this->context->render($this->message.' ', 'cyan');
        $this->context->write('> ');
    }

    /**
     * Check answer
     */
    protected function validate(string &$answer): bool
    {
        return true;
    }
}
