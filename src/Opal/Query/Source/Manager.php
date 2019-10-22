<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Source;

use Df;
use Df\Core\IApp;

use Df\Mesh\Job\ITransaction;
use Df\Mesh\Job\ITransactionAdapter;
use Df\Mesh\Job\ITransactionAware;
use Df\Mesh\Job\TTransactionAware;

use Df\Opal\Query\IField;
use Df\Opal\Query\ISource;
use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\Field\Virtual as VirtualField;

use DecodeLabs\Glitch;

class Manager implements ITransactionAware
{
    use TTransactionAware;

    protected $references = [];
    protected $sources = [];
    protected $parent;
    protected $app;

    /**
     * Init with
     */
    public function __construct(IApp $app)
    {
        $this->app = $app;
    }

    /**
     * Get app
     */
    public function getApp(): IApp
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
            throw Glitch::ERuntime('A source has already been defined as '.$alias);
        }

        $this->references[$alias] = $reference;
        $source = $reference->getSource();
        $this->sources[$source->getQuerySourceId()] = $source;

        if ($this->transaction && $source instanceof ITransactionAdapter) {
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
    public function normalizeSource($source): ISource
    {
        if (is_string($source)) {
            Glitch::incomplete('Lookup entity');
        } elseif (!$source instanceof ISource) {
            Glitch::incomplete('Other types of source!??');
        }

        return $source;
    }


    /**
     * Get source by id
     */
    public function getSource(string $sourceId): ?ISource
    {
        return $this->sources[$sourceId] ?? null;
    }

    /**
     * Remove source
     */
    public function removeSource(ISource $source): Manager
    {
        $id = $source->getQuerySourceId();
        unset($this->sources[$id]);

        foreach ($this->references as $alias => $reference) {
            if ($reference->getId() === $id) {
                unset($this->reference[$alias]);
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
    public function setTransaction(?ITransaction $transaction): ITransactionAware
    {
        $this->transaction = $transaction;

        foreach ($this->sources as $source) {
            if ($source instanceof ITransactionAdapter) {
                $this->transaction->registerAdapter($source);
            }
        }

        return $this;
    }



    /**
     * Find field and wrap with secondary alias
     */
    public function realiasField(string $field): IField
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
    public function findLocalField(string $name): IField
    {
        $parts = explode('.', $name, 2);
        $fieldName = array_pop($parts);
        $sourceAlias = array_shift($parts);

        if ($field = $this->lookupLocalField($sourceAlias, $fieldName, $possible)) {
            return $field;
        }

        if ($possible) {
            return $possible->findField($fieldName);
        }

        throw Glitch::EUnexpectedValue(
            'Field '.$name.' could not be found in current query'
        );
    }

    /**
     * Find foreign field by string reference
     */
    public function findForeignField(string $name, string $ignoreAlias=null): IField
    {
        $parts = explode('.', $name, 2);
        $fieldName = array_pop($parts);
        $sourceAlias = array_shift($parts);

        if ($field = $this->lookupForeignField($sourceAlias, $fieldName, $possible, $ignoreAlias)) {
            return $field;
        }

        if ($possible) {
            return $possible->findField($fieldName);
        }

        throw Glitch::EUnexpectedValue(
            'Field '.$name.' could not be found in current query'
        );
    }


    /**
     * Lookup local field
     */
    protected function lookupLocalField(?string $sourceAlias, string $name, ?Reference &$possible=null, string $ignoreAlias=null): ?IField
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
    protected function lookupForeignField(?string $sourceAlias, string $name, ?Reference &$possible=null, string $ignoreAlias=null): ?IField
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
