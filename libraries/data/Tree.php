<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data;

use df;
use df\data;

class Tree implements \IteratorAggregate, IHashMap, IValueProvider
{
    use namespace\arrayCollection\THashMap;
    use namespace\arrayCollection\TMutableHashMap;

    const MUTABLE = true;

    protected $value;

    /**
     * Value based construct
     */
    public function __construct(iterable $items=null, $value=null)
    {
        $this->value = $value;

        if ($items !== null) {
            $this->merge(data\Arr::iterableToArray($items));
        }
    }


    /**
     * Clone whole tree
     */
    public function __clone()
    {
        foreach ($this->items as $key => $child) {
            $this->items[$key] = clone $child;
        }

        return $this;
    }



    /**
     * Set node value
     */
    public function __set(string $key, $value): IHashMap
    {
        if (is_iterable($value)) {
            $items = $value;
            $value = null;
        } else {
            $items = [];
        }

        $this->items[$key] = new static($items, $value);
        return $this;
    }

    /**
     * Set value by dot access
     */
    public function setDot(string $key, $value, string $separator='.'): IHashMap
    {
        $node = $this->getDot($key, $separator);

        if (is_iterable($value)) {
            $node->clear()->merge($value);
        } else {
            $node->setValue($value);
        }

        return $this;
    }


    /**
     * Get node
     */
    public function __get(string $key): IHashMap
    {
        if (!array_key_exists($key, $this->items)) {
            $this->items[$key] = new static();
        }

        return $this->items[$key];
    }

    /**
     * Get value by dot access
     */
    public function getDot(string $key, string $separator='.')
    {
        $node = $this;

        foreach (explode($separator, $key) as $part) {
            $node = $node->{$part};
        }

        return $node;
    }


    /**
     * Check for node
     */
    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Remove node
     */
    public function __unset(string $key): IHashMap
    {
        unset($this->items[$key]);
        return $this;
    }



    /**
     * Get value
     */
    public function get(string $key)
    {
        if (!isset($this->items[$key])) {
            return null;
        }

        return $this->items[$key]->getValue();
    }

    /**
     * Set value on node
     */
    public function set(string $key, $value): IHashMap
    {
        $this->__get($key)->setValue($value);
        return $this;
    }

    /**
     * True if any provided keys have a set value (not null)
     */
    public function has(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (isset($this->items[$key]) && $this->items[$key]->hasValue()) {
                return true;
            }
        }

        return false;
    }

    /**
     * True if all provided keys have a set value (not null)
     */
    public function hasAll(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (!(isset($this->items[$key]) && $this->items[$key]->hasValue())) {
                return false;
            }
        }

        return true;
    }


    /**
     * Remove empty nodes
     */
    public function removeEmpty(): IHashMap
    {
        foreach ($this->items as $key => $node) {
            $node->removeEmpty();

            if ($node->isEmpty() && !$node->hasValue()) {
                unset($this->items[$key]);
            }
        }

        return $this;
    }

    /**
     * Lookup a key by value
     */
    public function findKey($value, bool $strict=false): ?string
    {
        foreach ($this->items as $key => $node) {
            if ($node->isValue($value, $strict)) {
                return (string)$key;
            }
        }

        return null;
    }


    /**
     * Reset all values
     */
    public function clear(): data\IHashMap
    {
        $this->value = null;
        $this->items = [];
        return $this;
    }




    /**
     * Set by array access
     */
    public function offsetSet($key, $value)
    {
        if ($key === null) {
            if (is_iterable($value)) {
                $this->items[] = new static($value);
            } else {
                $this->items[] = new static(null, $value);
            }
        } else {
            $key = (string)$key;
            $this->__set($key, $value);
        }

        return $this;
    }

    /**
     * Get by array access
     */
    public function offsetGet($key)
    {
        if (!isset($this->items[$key])) {
            return null;
        }

        return $this->items[$key]->getValue();
    }

    /**
     * Check by array access
     */
    public function offsetExists($key)
    {
        if (!isset($this->items[$key])) {
            return null;
        }

        return $this->items[$key]->hasValue();
    }




    /**
     * Set container value
     */
    public function setValue($value): IHashMap
    {
        if (is_iterable($value)) {
            return $this->merge($value);
        }

        $this->value = $value;
        return $this;
    }

    /**
     * Get container value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Check container value
     */
    public function hasValue(): bool
    {
        return $this->value !== null;
    }

    /**
     * Check container and children for value
     */
    public function hasAnyValue(): bool
    {
        if ($this->hasValue()) {
            return true;
        }

        foreach ($this->items as $key => $child) {
            if ($child->hasAnyValue()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compare value
     */
    public function isValue($value, bool $strict): bool
    {
        if ($strict) {
            return $value === $this->value;
        } else {
            return $value == $this->value;
        }
    }



    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }


    /**
     * From query string
     */
    public static function createFromDelimitedString(string $string, string $setDelimiter='&', string $valueDelimiter='='): Tree
    {
        $output = new static();
        $parts = explode($setDelimiter, $string);

        foreach ($parts as $part) {
            $valueParts = explode($valueDelimiter, trim($part), 2);

            $key = str_replace(['[', ']'], ['.', ''], urldecode(array_shift($valueParts)));
            $value = urldecode(array_shift($valueParts));

            $output->setDot($key, $value);
        }

        return $output;
    }


    /**
     * To query string
     */
    public function toDelimitedString(string $setDelimiter='&', string $valueDelimiter='='): string
    {
        $output = [];

        foreach ($this->toDelimitedSet(true) as $key => $value) {
            $key = rawurlencode($key);
            
            if (!empty($value) || $value === '0' || $value === 0) {
                $output[] = $key.$valueDelimiter.rawurlencode((string)$value);
            } else {
                $output[] = $key;
            }
        }

        return implode($setDelimiter, $output);
    }

    /**
     * Convert to delimited set
     */
    public function toDelimitedSet(bool $urlEncode=false, string $prefix=null): array
    {
        $output = [];

        if ($prefix !== null &&
            ($this->value !== null || empty($this->items))) {
            $output[$prefix] = $this->getValue();
        }

        foreach ($this->items as $key => $child) {
            if ($urlEncode) {
                $key = rawurlencode((string)$key);
            }

            if ($prefix !== null) {
                $key = $prefix.'['.$key.']';
            }

            $output = array_merge($output, $child->toDelimitedSet($urlEncode, (string)$key));
        }

        return $output;
    }


    /**
     * Map $values to values of collection as keys
     */
    public function combineWithValues(iterable $values): data\IHashMap
    {
        $items = array_map(function ($node) {
            return $node->getValue();
        }, $this->items);

        if (false !== ($result = array_combine($items, data\Arr::iterableToArray($values)))) {
            $this->clear()->merge($result);
        }

        return $this;
    }



    /**
     * Replace all values with $value
     */
    public function fill($value): data\IHashMap
    {
        $result = array_fill_keys(array_keys($this->items), $value);
        return $this->clear()->merge($result);
    }


    /**
     * Flip keys and values
     */
    public function flip(): data\IHashMap
    {
        $items = array_map(function ($node) {
            return (string)$node->getValue();
        }, $this->items);

        return $this->clear()->merge(array_flip($items));
    }



    /**
     * Merge all passed collections into one
     */
    public function merge(iterable ...$arrays): data\IHashMap
    {
        foreach ($arrays as $array) {
            if ($array instanceof Tree) {
                $this->value = $array->value;

                foreach ($array->items as $key => $node) {
                    if (isset($this->items[$key])) {
                        $this->items[$key]->merge($node);
                    } else {
                        $this->items[$key] = clone $node;
                    }
                }
            } else {
                foreach ($array as $key => $value) {
                    if (is_iterable($value)) {
                        if (isset($this->items[$key])) {
                            $this->items[$key]->merge($value);
                        } else {
                            $this->items[$key] = new static($value);
                        }
                    } else {
                        if (isset($this->items[$key])) {
                            $this->items[$key]->setValue($value);
                        } else {
                            $this->items[$key] = new static(null, $value);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Merge EVERYTHING :D
     */
    public function mergeRecursive(iterable ...$arrays): data\IHashMap
    {
        return $this->merge(...$arrays);
    }


    /**
     * Like merge, but replaces.. obvs
     */
    public function replace(iterable ...$arrays): data\IHashMap
    {
        foreach ($arrays as $array) {
            if ($array instanceof Tree) {
                $this->value = $array->value;

                foreach ($array->items as $key => $node) {
                    $this->items[$key] = clone $node;
                }
            } else {
                foreach ($array as $key => $value) {
                    if (is_iterable($value)) {
                        $this->items[$key] = new static($value);
                    } else {
                        $this->items[$key] = new static(null, $value);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Alias of replace
     */
    public function replaceRecursive(iterable ...$arrays): data\IHashMap
    {
        return $this->replace(...$arrays);
    }


    /**
     * Remove duplicates from collection
     */
    public function unique(int $flags=SORT_STRING): data\IHashMap
    {
        $items = array_map(function ($node) {
            return (string)$node->getValue();
        }, $this->items);

        $items = array_unique($items, $flags);
        return $this->keep(...array_map('strval', array_keys($items)));
    }


    /**
     * Recursive array conversion
     */
    public function toArray(): array
    {
        $output = [];

        foreach ($this->items as $key => $child) {
            if (!$child->isEmpty()) {
                $output[$key] = $child->toArray();
            } else {
                $output[$key] = $child->getValue();
            }
        }

        return $output;
    }

    /**
     * Get just item array
     */
    public function getChildren(): array
    {
        return $this->items;
    }



    /**
     * Get dump info
     */
    public function __debugInfo(): array
    {
        $output = [];

        foreach ($this->items as $key => $child) {
            if ($child instanceof self && empty($child->items)) {
                $output[$key] = $child->value;
            } else {
                $output[$key] = $child;
            }
        }

        if (empty($output)) {
            if ($this->value !== null) {
                return [
                    '⇒ value' => $this->value
                ];
            } else {
                return [];
            }
        }

        if ($this->value !== null) {
            $output = [
                '⇒ value' => $this->value,
                '⇒ children' => $output
            ];
        }

        return $output;
    }
}
