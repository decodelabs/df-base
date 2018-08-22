<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip\Widget;

use Df;
use Df\Clip\IContext;

class ProgressBar
{
    const EMPTY = '░';
    const FULL = '▓';

    protected $min = 0;
    protected $max = 100;

    protected $started = false;

    protected $context;

    /**
     * Init with context and style
     */
    public function __construct(IContext $context, float $min=0, float $max=100)
    {
        $this->context = $context;
        $this->setRange($min, $max);
    }



    /**
     * Set min
     */
    public function setMin(float $min): ProgressBar
    {
        $this->min = $min;
        return $this;
    }

    /**
     * Get min
     */
    public function getMin(): float
    {
        return $this->min;
    }

    /**
     * Set max
     */
    public function setMax(float $max): ProgressBar
    {
        $this->max = $max;
        return $this;
    }

    /**
     * Get max
     */
    public function getMax(): float
    {
        return $this->max;
    }

    /**
     * Set range
     */
    public function setRange(float $min, float $max): ProgressBar
    {
        $this->setMin($min);
        $this->setMax($max);
        return $this;
    }

    /**
     * Get range
     */
    public function getRange(): array
    {
        return [$this->min, $this->max];
    }



    /**
     * Render
     */
    public function advance(float $value): ProgressBar
    {
        //$this->context->write("\r");
        $width = min($this->context->getWidth(), 80);

        if ($value < $this->min) {
            $value = $this->min;
        }

        if ($value > $this->max) {
            $value = $this->max;
        }

        $space = $width - 9;
        $percent = ($value - $this->min) / ($this->max - $this->min);
        $chars = ceil($percent * $space);

        if ($percent < 0.99) {
            $color = '+yellow|bold';
        } else {
            $color = '+green|bold';
        }


        if ($this->started) {
            $this->context->write("\r");
            $this->context->write("\e[2A");
        } else {
            $this->context->write("\r");
            $this->started = true;
        }

        $this->context->render(str_repeat('-', $width), 'white|bold');
        $this->context->render('| ', '+white|bold');

        $this->context->render(str_repeat(self::FULL, (int)$chars), $color);
        $this->context->render(str_repeat(self::EMPTY, (int)($space - $chars)), '+dim');
        $this->context->render(str_pad(ceil($percent * 100).'%', 5, ' ', STR_PAD_LEFT), '+white|bold');

        $this->context->render(' |', 'white|bold');
        $this->context->render(str_repeat('-', $width), '+white');

        return $this;
    }


    /**
     * Finalise
     */
    public function complete(): ProgressBar
    {
        $this->context->writeLine();

        return $this;
    }
}
