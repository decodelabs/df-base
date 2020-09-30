<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Source;

use Df\Core\App;

use Df\Mesh\Job\Transaction;
use Df\Mesh\Job\TransactionAdapter;
use Df\Mesh\Job\TransactionAware;
use Df\Mesh\Job\TransactionAwareTrait;

use Df\Opal\Query\Field;
use Df\Opal\Query\Source;
use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\Field\Virtual as VirtualField;

use DecodeLabs\Glitch;
use DecodeLabs\Exceptional;

class Manager implements TransactionAware
{
    use TransactionAwareTrait;

    protected $references = [];
    protected $sources = [];
    protected $parent;
    protected $app;

    /**
     * Init with
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Get app
     */
    public function getApp(): App
    {
        return $this->app;
    }


    /**
     * Set parent
     */
    public function setParent(?Manager $parent): Manager
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get parent
     */
    public function getParent(): ?Manager
    {
        return $this->parent;
    }


    /**
     * Add a source for a query
     */
    public function addReference(Reference $reference): Manager
    {
        $alias = $reference->getAlias();

        if (isset($this->references[$alias])) {
            throw Exceptional::Runtime(
                'A source has already been defined as '.$alias
            );
        }

        $this->references[$alias] = $reference;
        $source = $reference->getSource();
        $this->sources[$source->getQuerySourceId()] = $source;

        if ($this->transaction && $source instanceof TransactionAdapter) {
            $this->transaction->registerAdapter($source);
        }

        return $this;
    }

    /**
     * Get reference by alias
     */
    public function getReference(string $alias): ?Reference
    {
        return $this->references[$alias] ?? null;
    }

    /**
     * Get all references
     */
    public function getReferences(): array
    {
        return $this->references;
    }



    /**
     * Ensure input is a source
     */
    public function normalizeSource($source): Source
    {
        if (is_string($source)) {
            Glitch::incomplete('Lookup entity');
        } elseif (!$source instanceof Source) {
            Glitch::incomplete('Other types of source!??');
        }

        return $source;
    }


    /**
     * Get source by id
     */
    public function getSource(string $sourceId): ?Source
    {
        return $this->sources[$sourceId] ?? null;
    }

    /**
     * Remove source
     */
    public function removeSource(Source $source): Manager
    {
        $id = $source->getQuerySourceId();
        unset($this->sources[$id]);

        foreach ($this->references as $alias => $reference) {
            if ($reference->getId() === $id) {
                unset($this->references[$alias]);
            }
        }

        return $this;
    }

    /**
     * Get all sources
     */
    public function getSources(): array
    {
        return $this->sources;
    }


    /**
     * Set transaction and add all current sources
     */
    public function setTransaction(?Transaction $transaction): TransactionAware
    {
        $this->transaction = $transaction;

        if ($this->transaction !== null) {
            foreach ($this->sources as $source) {
                if ($source instanceof TransactionAdapter) {
                    $this->transaction->registerAdapter($source);
                }
            }
        }

        return $this;
    }



    /**
     * Find field and wrap with secondary alias
     */
    public function realiasField(string $field): Field
    {
        if (preg_match('/(.+) as ([^ ]+)$/', $field, $matches)) {
            $name = $matches[1];
            $alias = $matches[2];
        } else {
            $name = $field;
            $alias = null;
        }

        $field = $this->findForeignField($name);

        return new VirtualField(
            $field->getSourceReference(),
            $field->getAlias(),
            $alias ?? $field->getAlias(),
            [$field]
        );
    }


    /**
     * Find local field by string reference
     */
    public function findLocalField(string $name): Field
    {
        $parts = explode('.', $name, 2);
        $fieldName = (string)array_pop($parts);
        $sourceAlias = array_shift($parts);

        if ($field = $this->lookupLocalField($sourceAlias, $fieldName, $possible)) {
            return $field;
        }

        if ($possible) {
            return $possible->findField($fieldName);
        }

        throw Exceptional::UnexpectedValue(
            'Field '.$name.' could not be found in current query'
        );
    }

    /**
     * Find foreign field by string reference
     */
    public function findForeignField(string $name, string $ignoreAlias=null): Field
    {
        $parts = explode('.', $name, 2);
        $fieldName = (string)array_pop($parts);
        $sourceAlias = array_shift($parts);

        if ($field = $this->lookupForeignField($sourceAlias, $fieldName, $possible, $ignoreAlias)) {
            return $field;
        }

        if ($possible) {
            return $possible->findField($fieldName);
        }

        throw Exceptional::UnexpectedValue(
            'Field '.$name.' could not be found in current query'
        );
    }


    /**
     * Lookup local field
     */
    protected function lookupLocalField(?string $sourceAlias, string $name, ?Reference &$possible=null, string $ignoreAlias=null): ?Field
    {
        if ($sourceAlias !== null) {
            if (isset($this->references[$sourceAlias])) {
                return $this->references[$sourceAlias]->findFieldByName($name);
            } else {
                return null;
            }
        }

        foreach ($this->references as $refAlias => $reference) {
            if ($refAlias === $ignoreAlias) {
                continue;
            }

            if ($field = $reference->findFieldByAlias($name)) {
                return $field;
            } elseif (null !== ($fields = $reference->getSourceFields())) {
                if (in_array($name, $fields)) {
                    return $reference->findFieldByName($name, true);
                }
            } else {
                if (!$possible) {
                    $possible = $reference;
                }
            }
        }

        return null;
    }

    /**
     * Lookup foreign field
     */
    protected function lookupForeignField(?string $sourceAlias, string $name, ?Reference &$possible=null, string $ignoreAlias=null): ?Field
    {
        if ($field = $this->lookupLocalField($sourceAlias, $name, $possible, $ignoreAlias)) {
            return $field;
        }

        if ($this->parent && ($field = $this->parent->lookupForeignField($sourceAlias, $name, $possible))) {
            return $field;
        }

        return null;
    }
}
