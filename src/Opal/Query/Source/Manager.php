<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Source;

use Df;
use Df\Core\IApp;

use Df\Opal\Query\ISource;
use Df\Opal\Query\Source\Reference;

class Manager
{
    protected $references = [];
    protected $sources = [];

    protected $app;

    /**
     * Init with
     */
    public function __construct(IApp $app)
    {
        $this->app = $app;
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
     * Get all sources
     */
    public function getSources(): array
    {
        return $this->sources;
    }
}
