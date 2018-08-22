<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip\Input;

use Df;
use Df\Clip\IContext;

class Question
{
    protected $message;
    protected $options = [];
    protected $showOptions = true;
    protected $strict = false;
    protected $default;

    protected $context;

    /**
     * Init with message
     */
    public function __construct(IContext $context, string $message, string $default=null)
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
     * Set available options
     */
    public function setOptions(string ...$options): Question
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get available options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Should options be shown?
     */
    public function setShowOptions(bool $show): Question
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
     * Set strict
     */
    public function setStrict(bool $strict): Question
    {
        $this->strict = $strict;
        return $this;
    }

    /**
     * Is strict?
     */
    public function isStrict(): bool
    {
        return $this->strict;
    }


    /**
     * Set default value
     */
    public function setDefaultValue(?string $default): Question
    {
        $this->default = $default;
        return $this;
    }

    /**
     * Get default value
     */
    public function getDefaultValue(): ?string
    {
        return $this->default;
    }


    /**
     * Ask the question
     */
    public function prompt(): ?string
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

        if (!empty($this->options) && $this->showOptions) {
            $this->context->render('[', '+white');
            $fDefault = $this->strict ? $this->default : trim(strtolower((string)$this->default));
            $first = true;
            $defaultFound = false;

            foreach ($this->options as $option) {
                if (!$first) {
                    $this->context->render('/', '+white');
                }

                $first = false;
                $fOption = $this->strict ? $option : trim(strtolower($option));
                $style = '+white';

                if ($fDefault === $fOption) {
                    $style .= '|bold|underline';
                    $defaultFound = true;
                }

                $this->context->render($option, $style);
            }

            if (!$defaultFound && $this->default !== null) {
                $this->context->render(' : ', '+white');
                $this->context->render($this->default, '+white|bold|underline');
            }

            $this->context->render(']', '+white');
        }

        $this->context->writeLine();
        $this->context->write('> ');
    }

    /**
     * Check answer
     */
    protected function validate(string &$answer): bool
    {
        if (empty($this->options)) {
            return true;
        }

        if (!strlen($answer) && $this->default !== null) {
            $answer = $this->default;
        }

        $testAnswer = $this->strict ? $answer : trim(strtolower($answer));
        $testOptions = [];

        foreach ($this->options as $option) {
            $fOption = $this->strict ? $option : trim(strtolower($option));
            $testOptions[$fOption] = $option;
        }

        if (!isset($testOptions[$answer])) {
            if ($answer === $this->default) {
                return true;
            } else {
                $this->context->render('Sorry, try again..', 'error');
                $this->context->writeLine();
                return false;
            }
        } else {
            $answer = $testOptions[$answer];
        }

        return true;
    }
}
