<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Html;

use Df;
use Df\Data\ArrayCollection\TSequence;

trait TElementContent
{
    use TSequence;

    /**
     * Render inner content
     */
    public function renderContent(): IContainer
    {
        $output = '';

        foreach ($this->items as $value) {
            if (empty($value) && $value != '0') {
                continue;
            }

            $output .= $this->renderChild($value);
        }

        return new Buffer($output);
    }

    /**
     * Convert child element to string
     */
    protected function renderChild($value): string
    {
        $output = '';

        if (is_callable($value) && is_object($value)) {
            return $this->renderChild($value($this));
        }

        if (is_iterable($value) && !$value instanceof IContainer) {
            foreach ($value as $part) {
                $output .= $this->renderChild($part);
            }

            return $output;
        }

        $output = (string)$value;

        if (!$value instanceof IContainer) {
            $output = $this->esc($output);
        }

        return $output;
    }
}
