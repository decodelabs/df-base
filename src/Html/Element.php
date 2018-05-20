<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Html;

use Df;
use Df\Data\ISequence;

class Element extends Tag implements \IteratorAggregate, ISequence
{
    use TElementContent;

    const MUTABLE = true;

    /**
     * Init with name, content and attributes
     */
    public function __construct(string $name, $content, array $attributes=null)
    {
        parent::__construct($name, $attributes);

        if (!is_iterable($content)) {
            $content = [$content];
        }

        $this->merge($content);
    }

    /**
     * Render to string
     */
    public function __toString(): string
    {
        return (string)$this->renderWith($this->renderContent());
    }

    /**
     * Replace all content with new body
     */
    public function setBody($body): Element
    {
        $this->clear()->push($body);
        return $this;
    }

    /**
     * Dump to string
     */
    public function __debugInfo(): array
    {
        return [
            'el' => $this->__toString()
        ];
    }
}
