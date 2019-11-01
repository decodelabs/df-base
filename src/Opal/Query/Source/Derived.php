<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Source;

use Df\Opal\Query\Source\Composed as ComposedSource;
use Df\Opal\Query\Builder\Derivable;

class Derived implements ComposedSource
{
    protected $name;
    protected $query;
    protected $source;

    /**
     * Init with derivable query
     */
    public function __construct(Derivable $query, string $name)
    {
        $this->query = $query;
        $this->source = $query->getPrimarySource();
        $this->name = $name;
    }

    /**
     * Get derivation source id
     */
    public function getQuerySourceId(): string
    {
        return 'derived('.$this->source->getQuerySourceId().','.$this->name.')';
    }

    /**
     * Get derivation source default alias
     */
    public function getDefaultQueryAlias(): string
    {
        return $this->source->getDefaultQueryAlias();
    }

    /**
     * Get derived field names
     */
    public function getFieldNames(): array
    {
        $manager = $this->query->getSourceManager();
        $output = [];

        foreach ($manager->getReferences() as $reference) {
            $output = array_merge(array_keys($reference->getFields()));
        }

        return array_unique($output);
    }



    /**
     * Render as pseudo SQL string
     */
    public function __toString(): string
    {
        return (string)$this->query;
    }
}
