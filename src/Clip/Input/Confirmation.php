<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip\Input;

use Df\Clip\IContext;
use Df\Flex\Formatter;

class Confirmation
{
    protected $message;
    protected $showOptions = true;
    protected $default;

    protected $context;

    /**
     * Init with message
     */
    public function __construct(IContext $context, string $message, bool $default=null)
    {
        $this->context = $context;
        $this->message = $message;
        $this->setDefaultValue($default);
    }

    /**
     * Get body of the question
     */
    public function getMessage(): string
    {
        return $this->message;
    }


    /**
     * Should options be shown?
     */
    public function setShowOptions(bool $show): Confirmation
    {
        $this->showOptinos = $show;
        return $this;
    }

    /**
     * Show options?
     */
    public function shouldShowOptions(): bool
    {
        return $this->showOptions;
    }


    /**
     * Set default value
     */
    public function setDefaultValue(?bool $default): Confirmation
    {
        $this->default = $default;
        return $this;
    }

    /**
     * Get default value
     */
    public function getDefaultValue(): ?bool
    {
        return $this->default;
    }


    /**
     * Ask the question
     */
    public function prompt(): bool
    {
        $done = false;

        while (!$done) {
            $this->renderQuestion();
            $answer = $this->context->shell->readLine();

            if ($this->validate($answer)) {
                $done = true;
            }
        }

        return $answer;
    }

    /**
     * Render question
     */
    protected function renderQuestion(): void
    {
        $this->context->render($this->message.' ', '+cyan');

        if ($this->showOptions) {
            $this->context->render('[', '+white');
            $this->context->render('y', '+white'.($this->default === true ? '|bold|underline' : null));
            $this->context->render('/', '+white');
            $this->context->render('n', '+white'.($this->default === false ? '|bold|underline' : null));
            $this->context->render(']', '+white');
        }

        $this->context->writeLine();
        $this->context->write('> ');
    }

    /**
     * Check answer
     */
    protected function validate(&$answer): bool
    {
        if (!strlen($answer) && $this->default !== null) {
            $answer = $this->default;
        }

        if (!is_bool($answer)) {
            $answer = Formatter::toBoolean($answer);
        }

        if ($answer === null) {
            $this->context->render('Sorry, try again..', 'error');
            $this->context->writeLine();
            return false;
        }

        return true;
    }
}
