<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Config;

use DecodeLabs\Fluidity\Then;
use DecodeLabs\Fluidity\ThenTrait;

use DecodeLabs\Exceptional;

class Env implements \ArrayAccess, Then
{
    use ThenTrait;

    protected $identity;
    protected $data = [];

    /**
     * Construct with ini data
     */
    public function __construct(string $identity, array $data)
    {
        $this->identity = $identity;

        foreach ($data as $key => $value) {
            if (!is_scalar($value)) {
                throw Exceptional::UnexpectedValue(
                    'Env value '.$key.' is not a scalar',
                    null,
                    $value
                );
            }

            $this->data[$key] = $value;
        }
    }

    /**
     * Get env identity, used for loading env specific config
     */
    public function getIdentity(): string
    {
        return $this->identity;
    }


    /**
     * Get string value
     */
    public function get(string $key, string $default=null): ?string
    {
        if (null === ($value = $this->data[$key] ?? null)) {
            $value = $default;
        }

        return $value;
    }

    /**
     * Get value as bool
     */
    public function getBool(string $key, bool $default=null): ?bool
    {
        if (null === ($value = $this->data[$key] ?? null)) {
            return $default;
        } else {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
    }

    /**
     * Get value as int
     */
    public function getInt(string $key, int $default=null): ?int
    {
        if (null === ($value = $this->data[$key] ?? null)) {
            return $default;
        } else {
            return (int)$value;
        }
    }

    /**
     * Get value as float
     */
    public function getFloat(string $key, float $default=null): ?float
    {
        if (null === ($value = $this->data[$key] ?? null)) {
            return $default;
        } else {
            return (float)$value;
        }
    }

    /**
     * Extract a list of values
     */
    public function getMap(string ...$keys): array
    {
        $output = [];

        foreach ($keys as $key) {
            $output[$key] = $this->get($key);
        }

        return $output;
    }

    /**
     * Get all values
     */
    public function getAll(): array
    {
        return $this->data;
    }




    /**
     * Set a value
     */
    public function set(string $key, string $value): Env
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Set a map of values
     */
    public function setMap(array $map): Env
    {
        foreach ($map as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Remove a value
     */
    public function remove(string ...$keys): Env
    {
        foreach ($keys as $key) {
            unset($this->data[$key]);
        }

        return $this;
    }


    /**
     * Check to see if keys are in map
     */
    public function has(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->data)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Cry if keys aren't set
     */
    public function checkExists(string ...$keys): Env
    {
        $failed = [];

        foreach ($keys as $key) {
            if (!isset($this->data[$key])) {
                $failed[] = $key;
            }
        }

        if (!empty($failed)) {
            throw Exceptional::Runtime(
                'Env key(s) '.implode(', ', $failed).' have not been set'
            );
        }

        return $this;
    }



    /**
     * Offset set
     */
    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Offset get
     */
    public function offsetGet($key): ?string
    {
        return $this->get($key);
    }

    /**
     * Offset exists
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Offset unset
     */
    public function offsetUnset($key): void
    {
        $this->remove($key);
    }
}
