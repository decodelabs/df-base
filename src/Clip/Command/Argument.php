<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip\Command;

use Df;
use Df\Flex\Formatter;

class Argument implements IArgument
{
    protected $name;
    protected $description;
    protected $shortcut;
    protected $named = false;
    protected $boolean = false;
    protected $optional = false;
    protected $list = false;
    protected $defaultValue;
    protected $pattern;


    /**
     * Init with name
     */
    public function __construct(string $name, string $description)
    {
        if (substr($name, 0, 1) == '-') {
            $this->setBoolean(true);
            $name = ltrim($name, '-');
        }

        if (false !== strpos($name, '=')) {
            $parts = explode('=', $name);
            $name = array_shift($parts);
            $this->setDefaultValue(array_shift($parts));
            $this->setBoolean(false);
        }

        if (false !== strpos($name, '|')) {
            $parts = explode('|', $name);
            $this->setShortcut(array_shift($parts));
            $name = array_shift($parts);
        }

        if (substr($name, -1) == '?') {
            $this->setOptional(true);
            $name = substr($name, 0, -1);
        }

        if (substr($name, -1) == '*') {
            $this->setList(true);
            $name = substr($name, 0, -1);
        }

        $this->name = $name;
        $this->setDescription($description);
    }

    /**
     * Get the name of the argument
     */
    public function getName(): string
    {
        return $this->name;
    }




    /**
     * Set description of argument
     */
    public function setDescription(string $description): IArgument
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description of argument
     */
    public function getDescription(): string
    {
        return $this->description;
    }


    /**
     * Set whether argument is named option
     */
    public function setNamed(bool $named): IArgument
    {
        $this->named = $named;
        return $this;
    }

    /**
     * Is this argument named?
     */
    public function isNamed(): bool
    {
        return $this->named;
    }



    /**
     * Set a single char shortcut
     */
    public function setShortcut(?string $shortcut): IArgument
    {
        if ($shortcut !== null) {
            $shortcut = substr($shortcut, 0, 1);
        }

        $this->shortcut = $shortcut;
        return $this;
    }

    /**
     * Get the single char shortcut
     */
    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }



    /**
     * Is this argument a boolean value?
     */
    public function setBoolean(bool $boolean): IArgument
    {
        if ($boolean) {
            $this->defaultValue = null;
            $this->optional = true;
            $this->named = true;
            $this->pattern = null;
        }

        $this->boolean = $boolean;
        return $this;
    }

    /**
     * Is this a boolean option?
     */
    public function isBoolean(): bool
    {
        return $this->boolean;
    }


    /**
     * Set whether argument is optional
     */
    public function setOptional(bool $optional): IArgument
    {
        $this->optional = $optional;

        if (!$optional) {
            $this->defaultValue = null;
        }

        return $this;
    }

    /**
     * Is argument optional?
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }


    /**
     * Set this as a list argument
     */
    public function setList(bool $list): IArgument
    {
        if ($list) {
            $this->setBoolean(false);
        }

        $this->list = $list;
        return $this;
    }

    /**
     * Is this a list argument?
     */
    public function isList(): bool
    {
        return $this->list;
    }


    /**
     * Set a default value
     */
    public function setDefaultValue(?string $value): IArgument
    {
        if (empty($value)) {
            $value = null;
        }

        $this->optional = true;
        $this->defaultValue = $value;
        return $this;
    }

    /**
     * Get default value
     */
    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }


    /**
     * Set a test reg pattern
     */
    public function setPattern(?string $pattern): IArgument
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * Get the test reg pattern
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }


    /**
     * Check and normalize input value
     */
    public function validate($value)
    {
        if ($this->boolean) {
            if (is_string($value)) {
                $value = Formatter::toBoolean($value);

                if ($value === null) {
                    throw Df\Error::EUnexpectedValue(
                        'Invalid boolean value found for argument: '.$this->name
                    );
                }
            }

            if ($value === true) {
                return true;
            } elseif ($value === false || $value === null) {
                return false;
            }
        } else {
            if ($value === null) {
                if (!$this->optional) {
                    throw Df\Error::EUnexpectedValue(
                        'No value found for argument: '.$this->name
                    );
                } else {
                    return $this->defaultValue;
                }
            }

            if ($this->pattern !== null && !mb_ereg($this->pattern, $value)) {
                throw Df\Error::EUnexpectedValue(
                    'Value does not match pattern for argument: '.$this->name
                );
            }

            return $value;
        }
    }
}