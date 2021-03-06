<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Source;

use Df\Opal\Query\Source\Composed as ComposedSource;
use Df\Opal\Query\Builder;
use Df\Opal\Query\Builder\Correlated as CorrelatedBuilder;

class Correlated implements ComposedSource
{
    protected $name;
    protected $query;
    protected $source;

    /**
     * Init with derivable query
     */
    public function __construct(CorrelatedBuilder $query, string $name)
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
        return 'correlated('.$this->source->getQuerySourceId().','.$this->name.')';
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
        return [$this->name];
    }

    /**
     * Get source query
     */
    public function getSubQuery(): CorrelatedBuilder
    {
        return $this->query;
    }
}
