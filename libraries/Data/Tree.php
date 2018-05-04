<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Data;

use Df;
use Df\Flex\Input\Sanitizer;

class Tree implements \IteratorAggregate, ITree
{
    use namespace\ArrayCollection\THashMap;

    const MUTABLE = true;
    const KEY_SEPARATOR = null;

    protected $value;

    /**
     * Value based construct
     */
    public function __construct(iterable $items=null, $value=null)
    {
        $this->value = $value;

        if ($items !== null) {
            $this->merge(Arr::iterableToArray($items));
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
     * Set value by dot access
     */
    public function setNode(string $key, $value): ITree
    {
        $node = $this->getNode($key);

        if (is_iterable($value)) {
            $node->clear()->merge($value);
        } else {
            $node->setValue($value);
        }

        return $this;
    }

    /**
     * Get node by dot access
     */
    public function getNode(string $key): ITree
    {
        if (static::KEY_SEPARATOR === null) {
            return $this->__get($key);
        }

        $node = $this;

        foreach (explode(static::KEY_SEPARATOR, $key) as $part) {
            $node = $node->__get($part);
        }

        return $node;
    }

    /**
     * True if any provided keys exist as a node
     */
    public function hasNode(string ...$keys): bool
    {
        if (static::KEY_SEPARATOR === null) {
            foreach ($keys as $key) {
                if (isset($this->items[$key])) {
                    return true;
                }
            }
        } else {
            foreach ($keys as $key) {
                $node = $this;

                foreach (explode(static::KEY_SEPARATOR, $key) as $part) {
                    if (!$node->__isset($part)) {
                        continue 2;
                    }

                    $node = $node->__get($part);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * True if all provided keys exist as a node
     */
    public function hasAllNodes(string ...$keys): bool
    {
        if (static::KEY_SEPARATOR === null) {
            foreach ($keys as $key) {
                if (!isset($this->items[$key])) {
                    return false;
                }
            }
        } else {
            foreach ($keys as $key) {
                $node = $this;

                foreach (explode(static::KEY_SEPARATOR, $key) as $part) {
                    if (!$node->__isset($part)) {
                        return false;
                    }

                    $node = $node->__get($part);
                }
            }
        }

        return true;
    }




    /**
     * Get value
     */
    public function get(string $key)
    {
        return $this->getNode($key)->getValue();
    }

    /**
     * Set value on node
     */
    public function set(string $key, $value): IHashMap
    {
        $this->getNode($key)->setValue($value);
        return $this;
    }

    /**
     * True if any provided keys have a set value (not null)
     */
    public function has(string ...$keys): bool
    {
        if (static::KEY_SEPARATOR === null) {
            foreach ($keys as $key) {
                if (isset($this->items[$key]) && $this->items[$key]->hasValue()) {
                    return true;
                }
            }
        } else {
            foreach ($keys as $key) {
                $node = $this;

                foreach (explode(static::KEY_SEPARATOR, $key) as $part) {
                    if (!$node->__isset($part)) {
                        continue 2;
                    }

                    $node = $node->__get($part);
                }

                if ($node->hasValue()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * True if all provided keys have a set value (not null)
     */
    public function hasAll(string ...$keys): bool
    {
        if (static::KEY_SEPARATOR === null) {
            foreach ($keys as $key) {
                if (!(isset($this->items[$key]) && $this->items[$key]->hasValue())) {
                    return false;
                }
            }
        } else {
            foreach ($keys as $key) {
                $node = $this;

                foreach (explode(static::KEY_SEPARATOR, $key) as $part) {
                    if (!$node->__isset($part)) {
                        return false;
                    }

                    $node = $node->__get($part);
                }

                if (!$node->hasValue()) {
                    return false;
                }
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
    public function clear(): IHashMap
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
            $node->set((string)$key, $value);
        }

        return $node;
    }

    /**
     * Get by array access
     */
    public function offsetGet($key)
    {
        return $this->getNode($key)->getValue();
    }

    /**
     * Check by array access
     */
    public function offsetExists($key)
    {
        return $this->getNode($key)->hasValue();
    }



    /**
     * Get node and return value sanitizer
     */
    public function sanitize(string $key): Sanitizer
    {
        return $this->getNode($key)->sanitizeValue();
    }

    /**
     * Return new Sanitizer with node value
     */
    public function sanitizeValue(): Sanitizer
    {
        return new Sanitizer($this->getValue());
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
    public static function createFromDelimitedString(string $string, string $setDelimiter='&', string $valueDelimiter='='): ITree
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
    public function combineWithValues(iterable $values): IHashMap
    {
        $items = array_map(function ($node) {
            return $node->getValue();
        }, $this->items);

        if (false !== ($result = array_combine($items, Arr::iterableToArray($values)))) {
            $this->clear()->merge($result);
        }

        return $this;
    }



    /**
     * Replace all values with $value
     */
    public function fill($value): IHashMap
    {
        $result = array_fill_keys(array_keys($this->items), $value);
        return $this->clear()->merge($result);
    }


    /**
     * Flip keys and values
     */
    public function flip(): IHashMap
    {
        $items = array_map(function ($node) {
            return (string)$node->getValue();
        }, $this->items);

        return $this->clear()->merge(array_flip($items));
    }



    /**
     * Merge all passed collections into one
     */
    public function merge(iterable ...$arrays): IHashMap
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
    public function mergeRecursive(iterable ...$arrays): IHashMap
    {
        return $this->merge(...$arrays);
    }


    /**
     * Like merge, but replaces.. obvs
     */
    public function replace(iterable ...$arrays): IHashMap
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
    public function replaceRecursive(iterable ...$arrays): IHashMap
    {
        return $this->replace(...$arrays);
    }


    /**
     * Remove duplicates from collection
     */
    public function unique(int $flags=SORT_STRING): IHashMap
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