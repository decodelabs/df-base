<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df;

use Df\Mesh\Job\ITransaction;
use Df\Mesh\Job\ITransactionAware;

use Df\Opal\Query\IBuilder;
use Df\Opal\Query\ISource;
use Df\Opal\Query\Field\INamed as INamedField;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

class Nest implements
    INested,
    IParentAware,
    IFieldCollector
{
    use TSources;
    use TParentAware;

    protected $name;
    protected $copy = false;
    protected $fields = [];
    protected $keyFields;

    /**
     * Init with query and fields
     */
    public function __construct(IBuilder $parentQuery, array $fields)
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
    public function setFields(string ...$fields): IFieldCollector
    {
        return $this->clearFields()->addField(...$fields);
    }

    /**
     * Add list of fields to nest
     */
    public function addFields(string ...$fields): IFieldCollector
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
    public function clearFields(): IFieldCollector
    {
        $this->fields = [];
        return $this;
    }



    /**
     * Set key field
     */
    public function withKey(string ...$fields): INested
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
    public function clearKeyFields(): INested
    {
        $this->keyFields = null;
        return $this;
    }


    /**
     * Set instruction name
     */
    public function setName(string $name): INested
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
            throw Df\Error::ELogic('No name has been given to nest instruction');
        }

        return $this->name;
    }



    /**
     * Mark instruction as a copy
     */
    public function setCopy(bool $copy): INested
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
    public function as(string $name): INestable
    {
        return $this->endNest($name);
    }


    /**
     * End nest and mark as copy
     */
    public function asCopy(string $name): INestable
    {
        return $this->setCopy(true)
            ->endNest($name);
    }


    /**
     * End nest instruction and register
     */
    public function endNest(?string $name=null): INestable
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
            if ($field instanceof INamedField) {
                $fieldName = (string)$field;
            } else {
                $fieldName = '*'.$field->getAlias();
            }

            $fields[] = str_replace("\n", "\n    ", $fieldName);
        }

        $output .= '    '.implode("\n    ", $fields)."\n";

        foreach ($this->keyFields as $field) {
            if ($field instanceof INamedField) {
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
