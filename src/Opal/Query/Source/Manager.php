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

use Df\Opal\Query\ISource;
use Df\Opal\Query\Source\Reference;

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
            throw Df\Error::ERuntime('A source has already been defined as '.$alias);
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
            Df\incomplete('Lookup entity');
        } elseif (!$source instanceof ISource) {
            Df\incomplete('Other types of source!??');
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
}
