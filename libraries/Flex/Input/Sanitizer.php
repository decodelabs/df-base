<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Flex\Input;

use Df;
use Df\Flex\Formatter;
use Df\Lang\Constraint\TRequirable;

class Sanitizer
{
    use TRequirable;

    protected $value;

    /**
     * Init with raw value
     */
    public function __construct($value, bool $nullable=false)
    {
        $this->value = $value;
        $this->required = !$nullable;
    }


    /**
     * Get original value
     */
    public function asRaw()
    {
        return $this->value;
    }

    /**
     * Get value as boolean
     */
    public function asBool($default=null): ?bool
    {
        $value = $this->prepareValue($default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /**
     * Get value as int
     */
    public function asInt($default=null): ?int
    {
        return (int)$this->prepareValue($default);
    }

    /**
     * Get value as float
     */
    public function asFloat($default=null): ?float
    {
        return (float)$this->prepareValue($default);
    }

    /**
     * Get value as string
     */
    public function asString($default=null): ?string
    {
        return (string)$this->prepareValue($default);
    }

    /**
     * Get value as slug string
     */
    public function asSlug($default=null): ?string
    {
        return Formatter::slug($this->prepareValue($default));
    }

    /**
     * Prepare output value
     */
    protected function prepareValue($default=null)
    {
        $value = $this->value ?? $default;

        if ($value instanceof \Callback) {
            $value = $value();
        }

        if ($this->required && $value === null) {
            throw Df\Error::EUnexpectedValue(
                'Value is required'
            );
        }

        return $value;
    }

    /**
     * Sanitize value using callback
     */
    public function with(callable $callback)
    {
        $value = $callback($this->value);

        if ($this->required && $value === null) {
            throw Df\Error::EUnexpectedValue(
                'Value is required'
            );
        }

        return $value;
    }
}
