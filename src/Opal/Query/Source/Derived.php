<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Source;

use Df;
use Df\Opal\Query\IComposedSource;
use Df\Opal\Query\Builder\IDerivable;

class Derived implements IComposedSource
{
    protected $name;
    protected $query;
    protected $source;

    /**
     * Init with derivable query
     */
    public function __construct(IDerivable $query, string $name)
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
        Df\incomplete('Extract output fields from manager');

        return $this->query->getOutputManifest()->getFieldNames();
    }



    /**
     * Render as pseudo SQL string
     */
    public function __toString(): string
    {
        return (string)$this->query;
    }
}
