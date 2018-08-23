<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Initiator;

use Df;
use Df\Core\IApp;

use Df\Opal\Query\IInitiator;
use Df\Opal\Query\IBuilder;
use Df\Opal\Query\Source\Manager as SourceManager;
use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\Builder\Select as SelectBuilder;

class Select implements
    IInitiator,
    IFieldCollector,
    IFromSource
{
    use TFieldCollector;

    protected $distinct = false;
    protected $parent;
    protected $app;

    /**
     * Init with fields and distinct
     */
    public function __construct(IApp $app, array $fields, bool $distinct=false)
    {
        $this->app = $app;
        $this->importFields($fields);
        $this->setDistinct($distinct);
    }



    /**
     * Set as disctinct
     */
    public function setDistinct(bool $distinct): Select
    {
        $this->distinct = $distinct;
        return $this;
    }

    /**
     * Is distinct
     */
    public function isDistinct(): bool
    {
        return $this->distinct;
    }


    /**
     * Set derivation parent
     */
    public function setDerivationParent(?IInitiator $parent): IInitiator
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get derivation parent
     */
    public function getDerivationParent(): ?IInitiator
    {
        return $this->parent;
    }


    /**
     * Set source and
     */
    public function from($source, string $alias=null): IBuilder
    {
        $manager = new SourceManager($this->app);
        $source = $manager->normalizeSource($source);

        $reference = new Reference($source, $alias);
        $manager->addReference($reference);

        foreach ($this->fields as $field) {
            $reference->selectField($field);
        }

        return new SelectBuilder($manager, $reference);
    }


    /**
     * Select from subquery
     */
    public function fromSelect(string ...$fields): Select
    {
        $output = new self($this->app, $fields);
        $output->setDerivationParent($this);
        return $output;
    }

    /**
     * Select from distinct subquery
     */
    public function fromSelectDistinct(string ...$fields): Select
    {
        $output = new self($this->app, $fields, true);
        $output->setDerivationParent($this);
        return $output;
    }

    /**
     * Select from union subquery
     */
    public function fromUnion(): Union
    {
        Df\incomplete();
    }
}
