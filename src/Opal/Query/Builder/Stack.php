<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Field;

class Stack
{
    protected $name;
    protected $mode;
    protected $query;
    protected $keyField;
    protected $valueField;
    protected $processor;

    /**
     * Init with name and query
     */
    public function __construct(string $name, Builder $builder, string $mode)
    {
        $this->name = $name;
        $this->query = $builder;
        $this->mode = $mode;
    }

    /**
     * Get stack name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get stack mode
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Get sub query
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }

    /**
     * Set key field
     */
    public function setKeyField(?Field $field): Stack
    {
        $this->keyField = $field;
        return $this;
    }

    /**
     * Get key field
     */
    public function getKeyField(): ?Field
    {
        return $this->keyField;
    }

    /**
     * Set value field
     */
    public function setValueField(?Field $field): Stack
    {
        $this->valueField = $field;
        return $this;
    }

    /**
     * Get value field
     */
    public function getValueField(): ?Field
    {
        return $this->valueField;
    }

    /**
     * Set processor
     */
    public function setProcessor(?callable $processor): Stack
    {
        $this->processor = $processor;
        return $this;
    }

    /**
     * Get processor
     */
    public function getProcessor(): ?callable
    {
        return $this->processor;
    }



    /**
     * Render to pseudo SQL string
     */
    public function __toString(): string
    {
        $mode = strtoupper($this->mode);

        if ($this->keyField || $this->valueField || $this->processor) {
            $args = [];

            if ($this->keyField) {
                $args[] = $this->keyField->getAlias();
            }

            if ($this->valueField) {
                $args[] = $this->valueField->getAlias();
            }

            if ($this->processor) {
                $args[] = '%';
            }

            $mode .= '('.implode(',', $args).')';
        }

        $output = 'STACK '.$mode.' FROM ('.$this->query.') as '.$this->name;
        return $output;
    }
}
