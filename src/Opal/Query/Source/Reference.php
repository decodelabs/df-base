<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Source;

use Df;
use Df\Opal\Query\ISource;
use Df\Opal\Query\IComposedSource;

use Df\Opal\Query\IField;
use Df\Opal\Query\Field\INamed as INamedField;
use Df\Opal\Query\Field\Wildcard;
use Df\Opal\Query\Field\Factory;

class Reference
{
    protected $fields = [];

    protected $alias;
    protected $prefix;
    protected $source;
    protected $sourceFields;

    /**
     * Init with source and alias
     */
    public function __construct(ISource $source, string $alias=null, string $prefix=null)
    {
        $this->source = $source;

        if ($alias === null) {
            $alias = $source->getDefaultQueryAlias();
        }

        $this->alias = $alias;
        $this->prefix = $prefix;
    }

    /**
     * Get source
     */
    public function getSource(): ISource
    {
        return $this->source;
    }

    /**
     * Get alias
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * Get alias prefix
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * Get prefixed alias
     */
    public function getPrefixedAlias(): string
    {
        $alias = $this->getAlias();

        if ($this->prefix !== null) {
            $alias = $this->prefix.':'.$alias;
        }

        return $alias;
    }

    /**
     * Get source id
     */
    public function getId(): string
    {
        return $this->source->getQuerySourceId();
    }

    /**
     * Get source fields
     */
    public function getSourceFields(): ?array
    {
        if (!$this->source instanceof IComposedSource) {
            return null;
        }

        if ($this->sourceFields === null) {
            $this->sourceFields = $this->source->getFieldNames();
        }

        return $this->sourceFields;
    }


    /**
     * Lookup or select field by name
     */
    public function findFieldByName(string $name, bool $create=false): ?IField
    {
        foreach ($this->fields as $field) {
            if ($field instanceof INamedField && $field->getName() === $name) {
                return $field;
            }
        }

        if ($create) {
            return (new Factory())->fromString($name, $this);
        }

        return null;
    }

    /**
     * Lookup or select field by alias
     */
    public function findFieldByAlias(string $alias): ?IField
    {
        return $this->fields[$alias] ?? null;
    }

    /**
     * Register field
     */
    public function selectField(string $field): IField
    {
        $field = (new Factory())->fromString($field, $this);

        if ($field instanceof Wildcard && ($sourceFields = $this->getSourceFields())) {
            foreach ($sourceFields as $sourceField) {
                $this->selectField($sourceField);
            }
        } else {
            $this->registerField($field);
        }

        return $field;
    }

    /**
     * Direct register field
     */
    public function registerField(IField $field): Reference
    {
        $alias = $field->getAlias();

        if (isset($this->fields[$alias]) && !$this->fields[$alias]->matches($field)) {
            throw Df\Error::EUnexpectedValue('Another field has already been aliased as '.$alias);
        }

        $this->fields[$field->getAlias()] = $field;
        return $this;
    }


    /**
     * Get list of fields
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Is source derived?
     */
    public function isDerived(): bool
    {
        return $this->source instanceof Derived ||
            $this->source instanceof Correlated;
    }

    /**
     * Is source correlated?
     */
    public function isCorrelated(): bool
    {
        return $this->source instanceof Correlated;
    }


    /**
     * Render to pseudo SQL string
     */
    public function __toString(): string
    {
        if ($this->isDerived()) {
            $output = '('.str_replace("\n", "\n  ", $this->source).')';
        } else {
            $output = $this->getId();
        }

        return $output.' as '.$this->getPrefixedAlias();
    }


    /**
     * Dump info
     */
    public function __debugInfo(): array
    {
        $fields = [];

        foreach ($this->fields as $alias => $field) {
            $fields[$alias] = (string)$field;
        }

        return [
            'source' => $this->getId().' as '.$this->alias,
            'fields' => $fields
        ];
    }
}
