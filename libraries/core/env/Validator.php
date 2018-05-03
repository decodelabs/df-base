<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\core\env;

use df;

class Validator implements IValidator
{
    protected $name;
    protected $value;

    public function __construct(string $name, ?string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Get key name
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * Get original value
     */
    public function asString(string $default=null): ?string
    {
        if ($this->value === null) {
            return $default;
        }

        return $this->value;
    }

    /**
     * Convert to bool
     */
    public function asBool(bool $default=null): ?bool
    {
        if (null !== ($val = filter_var($this->value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))) {
            return $val;
        }

        if ($this->value === null) {
            return $default;
        }

        return (bool)$this->value;
    }

    /**
     * Convert to int
     */
    public function asInt(int $default=null): ?int
    {
        if ($this->value === null) {
            return $default;
        }

        return (int)$this->value;
    }

    /**
     * Convert to float
     */
    public function asFloat(float $default=null): ?float
    {
        if ($this->value === null) {
            return $default;
        }

        return (float)$this->value;
    }



    /**
     * Is value empty?
     */
    public function isEmpty(): bool
    {
        return strlen(trim($this->value)) > 0;
    }

    /**
     * Is value a boolean?
     */
    public function isBool(): bool
    {
        return (filter_var($this->value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null);
    }

    /**
     * Is value an integer?
     */
    public function isInt(): bool
    {
        return ctype_digit($this->value);
    }

    /**
     * Is value a float?
     */
    public function isFloat(): bool
    {
        return is_numeric($this->value);
    }

    /**
     * Is value in one of these options?
     */
    public function isIn(...$values): bool
    {
        return in_array($this->value, $values);
    }




    /**
     * Cry if empty
     */
    public function checkEmpty(): IValidator
    {
        if (!$this->isEmpty()) {
            throw df\Error::EUnexpectedValue($this->name.' env value is empty');
        }

        return $this;
    }

    /**
     * Cry if not bool
     */
    public function checkBool(): IValidator
    {
        if (!$this->isBool()) {
            throw df\Error::EUnexpectedValue(
                $this->name.' env value is not a boolean',
                null,
                $this->value
            );
        }

        return $this;
    }

    /**
     * Cry if not int
     */
    public function checkInt(): IValidator
    {
        if (!$this->isInt()) {
            throw df\Error::EUnexpectedValue(
                $this->name.' env value is not an integer',
                null,
                $this->value
            );
        }

        return $this;
    }

    /**
     * Cry if not float
     */
    public function checkFloat(): IValidator
    {
        if (!$this->isFloat()) {
            throw df\Error::EUnexpectedValue(
                $this->name.' env value is not an float',
                null,
                $this->value
            );
        }

        return $this;
    }

    /**
     * Cry if not in list
     */
    public function checkIn(...$values): IValidator
    {
        if (!$this->isIn($values)) {
            throw df\Error::EUnexpectedValue(
                $this->name.' env value is not one of: '.implode(', ', $values),
                null,
                $this->value
            );
        }

        return $this;
    }
}
