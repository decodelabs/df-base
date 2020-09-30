<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Builder\FieldCollector;
use Df\Opal\Query\Builder\Nestable;
use Df\Opal\Query\Builder\Nested;
use Df\Opal\Query\Builder\SourceProviderTrait;
use Df\Opal\Query\Builder\ParentAware;
use Df\Opal\Query\Builder\ParentAwareTrait;

use Df\Opal\Query\Source;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

use Df\Opal\Query\Field\Named as NamedField;

use DecodeLabs\Exceptional;

class Nest implements
    Nested,
    ParentAware,
    FieldCollector
{
    use SourceProviderTrait;
    use ParentAwareTrait;

    protected $name;
    protected $copy = false;
    protected $fields = [];
    protected $keyFields;

    /**
     * Init with query and fields
     */
    public function __construct(Builder $parentQuery, array $fields)
    {
        $this->parentQuery = $parentQuery;
        $this->addFields(...$fields);
    }

    /**
     * Get source manager
     */
    public function getSourceManager(): Manager
    {
        return $this->parentQuery->getSourceManager();
    }

    /**
     * Get main source ref
     */
    public function getPrimarySourceReference(): Reference
    {
        return $this->parentQuery->getPrimarySourceReference();
    }



    /**
     * Set list of fields to nest
     */
    public function setFields(string ...$fields): FieldCollector
    {
        return $this->clearFields()->addFields(...$fields);
    }

    /**
     * Add list of fields to nest
     */
    public function addFields(string ...$fields): FieldCollector
    {
        $manager = $this->getSourceManager();

        foreach ($fields as $field) {
            $field = $manager->realiasField($field);
            $this->fields[$field->getAlias()] = $field;
        }

        return $this;
    }

    /**
     * Get registered fields
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Remove all registered fields
     */
    public function clearFields(): FieldCollector
    {
        $this->fields = [];
        return $this;
    }



    /**
     * Set key field
     */
    public function withKey(string ...$fields): Nested
    {
        $manager = $this->getSourceManager();

        foreach ($fields as $field) {
            if (isset($this->fields[$field])) {
                $this->keyFields[$field] = $this->fields[$field];
            } else {
                $field = $manager->findForeignField($field);
                $this->keyFields[$field->getAlias()] = $field;
            }
        }

        return $this;
    }

    /**
     * Get named key field
     */
    public function getKeyFields(): ?array
    {
        return $this->keyFields;
    }

    /**
     * Remove key fields
     */
    public function clearKeyFields(): Nested
    {
        $this->keyFields = null;
        return $this;
    }


    /**
     * Set instruction name
     */
    public function setName(string $name): Nested
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get given name for nest
     */
    public function getName(): string
    {
        if ($this->name === null) {
            throw Exceptional::Logic(
                'No name has been given to nest instruction'
            );
        }

        return $this->name;
    }



    /**
     * Mark instruction as a copy
     */
    public function setCopy(bool $copy): Nested
    {
        $this->copy = $copy;
        return $this;
    }

    /**
     * Do we copy or move?
     */
    public function isCopy(): bool
    {
        return $this->copy;
    }




    /**
     * Alias of endNest()
     */
    public function as(string $name): Nestable
    {
        return $this->endNest($name);
    }


    /**
     * End nest and mark as copy
     */
    public function asCopy(string $name): Nestable
    {
        return $this->setCopy(true)
            ->endNest($name);
    }


    /**
     * End nest instruction and register
     */
    public function endNest(?string $name=null): Nestable
    {
        if ($name !== null) {
            $this->setName($name);
        }

        if ($this->applicator) {
            ($this->applicator)($this);
        }

        $this->parentQuery->addNest($this);
        return $this->parentQuery;
    }

    /**
     * Render to pseudo SQL string
     */
    public function __toString(): string
    {
        $output = 'NEST';

        if ($this->copy) {
            $output .= ' COPY';
        }

        $output .= "\n";
        $fields = $keys = [];

        foreach ($this->fields as $field) {
            if ($field instanceof NamedField) {
                $fieldName = (string)$field;
            } else {
                $fieldName = '*'.$field->getAlias();
            }

            $fields[] = str_replace("\n", "\n    ", $fieldName);
        }

        $output .= '    '.implode("\n    ", $fields)."\n";

        foreach ($this->keyFields as $field) {
            if ($field instanceof NamedField) {
                $fieldName = (string)$field;
            } else {
                $fieldName = '*'.$field->getAlias();
            }

            $keys[] = str_replace("\n", "\n    ", $fieldName);
        }

        if (!empty($keys)) {
            $output .= '  KEY ON'."\n";
            $output .= '    '.implode("\n    ", $keys)."\n";
        }

        $output .= '  AS '.$this->name;

        return $output;
    }
}
