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
use Df\Opal\Query\Field\Wildcard;
use Df\Opal\Query\Field\Factory;

class Reference
{
    protected $fields = [];

    protected $alias;
    protected $source;
    protected $sourceFields;

    /**
     * Init with source and alias
     */
    public function __construct(ISource $source, string $alias=null)
    {
        $this->source = $source;

        if ($alias === null) {
            $alias = $source->getDefaultQueryAlias();
        }

        $this->alias = $alias;
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
     * Register field
     */
    public function selectField(string $field): IField
    {
        $factory = new Factory();
        $field = $factory->fromString($field, $this);
        $alias = $field->getAlias();

        if (isset($this->fields[$alias]) && !$this->fields[$alias]->matches($field)) {
            throw Df\Error::EUnexpectedValue('Another field has already been aliased as '.$alias);
        }

        if ($field instanceof Wildcard && ($sourceFields = $this->getSourceFields())) {
            foreach ($sourceFields as $sourceField) {
                $this->selectField($sourceField);
            }
        } else {
            $this->fields[$alias] = $field;
        }

        return $field;
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
