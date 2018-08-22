<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip\Widget;

use Df;
use Df\Clip\IContext;

class Spinner
{
    const TICK = 0.08;
    const CHARS = ['-', '\\', '|', '/'];

    protected $style;
    protected $context;

    protected $lastTime;
    protected $char = 0;

    /**
     * Init with context and style
     */
    public function __construct(IContext $context, string $style=null)
    {
        $this->context = $context;
        $this->setStyle($style);
    }


    /**
     * Set style
     */
    public function setStyle(?string $style): Spinner
    {
        $this->style = $style;
        return $this;
    }

    /**
     * Get style
     */
    public function getStyle(): ?string
    {
        return $this->style;
    }



    /**
     * Render
     */
    public function advance(): Spinner
    {
        $time = microtime(true);

        if ($this->lastTime + self::TICK > $time) {
            return $this;
        }

        if ($this->lastTime !== null) {
            $this->context->write("\x08");
        }

        $style = $this->context->styles->extractString($this->style);
        $char = self::CHARS[$this->char];
        $this->char++;

        if (!isset(self::CHARS[$this->char])) {
            $this->char = 0;
        }

        $this->context->write(
            $this->context->styles->format($char, $style[0], $style[1], ...$style[2])
        );

        $this->lastTime = $time;

        return $this;
    }


    /**
     * Finalise
     */
    public function complete(?string $message=null): Spinner
    {
        if ($this->lastTime !== null) {
            $this->context->write("\x08");
        }

        if ($message !== null) {
            $this->context->render($message, 'green|bold');
        }

        return $this;
    }
}
