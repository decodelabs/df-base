<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Data;

use Df;

trait TAttributeContainer
{
    protected $attributes = [];

    /**
     * Replace all attributes with new map
     */
    public function setAttributes(array $attributes): IAttributeContainer
    {
        return $this->clearAttributes()->addAttributes($attributes);
    }

    /**
     * Merge current attributes with new map
     */
    public function addAttributes(array $attributes): IAttributeContainer
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Get map of current attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Replace single value
     */
    public function setAttribute(string $key, $value): IAttributeContainer
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Retrieve attribute value if set
     */
    public function getAttribute(string $key)
    {
        if (!isset($this->attributes[$key])) {
            return null;
        }

        return $this->attributes[$key];
    }

    /**
     * Remove single attribute
     */
    public function removeAttribute(string ...$keys): IAttributeContainer
    {
        foreach ($keys as $key) {
            unset($this->attributes[$key]);
        }

        return $this;
    }

    /**
     *  Has this attribute been set?
     */
    public function hasAttribute(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (isset($this->attributes[$key])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove all attributes
     */
    public function clearAttributes(): IAttributeContainer
    {
        $this->attributes = [];
        return $this;
    }

    /**
     * How many attributes have been set?
     */
    public function countAttributes(): int
    {
        return count($this->attributes);
    }
}
