<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Html;

use Df;
use Df\Data\IArrayProvider;

class ClassList implements IArrayProvider, \Countable
{
    protected $classes = [];

    /**
     * Init with list
     */
    public function __construct(string ...$classes)
    {
        $this->add(...$classes);
    }

    /**
     * Add class list
     */
    public function add(string ...$classes): ClassList
    {
        foreach ($classes as $value) {
            foreach (explode(' ', $value) as $class) {
                $this->classes[$class] = true;
            }
        }

        return $this;
    }

    /**
     * Has class(es) in list
     */
    public function has(string ...$classes): bool
    {
        foreach ($classes as $class) {
            if (isset($this->classes[$class])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Has all classes in list
     */
    public function hasAll(string ...$classes): bool
    {
        foreach ($classes as $class) {
            if (!isset($this->classes[$class])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Clear class list
     */
    public function clear(): ClassList
    {
        $this->classes = [];
        return $this;
    }

    /**
     * How many classes in list?
     */
    public function count(): int
    {
        return count($this->classes);
    }

    /**
     * Export to array
     */
    public function toArray(): array
    {
        return array_keys($this->classes);
    }

    /**
     * Render to string
     */
    public function __toString(): string
    {
        return implode(' ', array_keys($this->classes));
    }

    /**
     * Dump list
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }
}
