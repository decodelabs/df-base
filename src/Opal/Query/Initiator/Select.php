<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Initiator;

use Df\Core\App;
use Df\Mesh\Job\TransactionAwareTrait;

use Df\Opal\Query\Initiator;
use Df\Opal\Query\Initiator\FieldCollector;
use Df\Opal\Query\Initiator\FieldCollectorTrait;
use Df\Opal\Query\Initiator\FromSource;
use Df\Opal\Query\Initiator\FromSourceTrait;
use Df\Opal\Query\Builder;
use Df\Opal\Query\Source\Manager as SourceManager;
use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\Builder\Select as SelectBuilder;

class Select implements
    Initiator,
    FieldCollector,
    FromSource
{
    use FieldCollectorTrait;
    use TransactionAwareTrait;
    use FromSourceTrait;

    protected $distinct = false;
    protected $parentQuery;
    protected $subQueryMode;
    protected $derivationParent;
    protected $applicator;
    protected $app;

    /**
     * Init with fields and distinct
     */
    public function __construct(App $app, array $fields, bool $distinct=false)
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
     * Use as subquery
     */
    public function asSubQuery(Builder $parent, string $mode, ?callable $applicator=null): Initiator
    {
        $this->setParentQuery($parent);
        $this->setSubQueryMode($mode, $applicator);
        return $this;
    }

    /**
     * Set parent query
     */
    public function setParentQuery(?Builder $parent): Initiator
    {
        $this->parentQuery = $parent;
        return $this;
    }

    /**
     * Get parent query
     */
    public function getParentQuery(): ?Builder
    {
        return $this->parentQuery;
    }

    /**
     * Set sub query mode
     */
    public function setSubQueryMode(?string $mode, ?callable $applicator=null): Initiator
    {
        $this->subQueryMode = $mode;
        $this->applicator = $applicator;
        return $this;
    }

    /**
     * Get sub query mode
     */
    public function getSubQueryMode(): ?string
    {
        return $this->subQueryMode;
    }


    /**
     * Set derivation parent
     */
    public function setDerivationParent(?Initiator $parent): Initiator
    {
        $this->derivationParent = $parent;
        return $this;
    }

    /**
     * Get derivation parent
     */
    public function getDerivationParent(): ?Initiator
    {
        return $this->derivationParent;
    }


    /**
     * Set source and alias
     */
    public function from($source, string $alias=null): Builder
    {
        $manager = new SourceManager($this->app);
        $manager->setTransaction($this->transaction);
        $source = $manager->normalizeSource($source);

        $reference = new Reference($source, $alias, $this->getAliasPrefix());
        $manager->addReference($reference);

        foreach ($this->fields as $field) {
            $reference->selectField($field);
        }

        $output = new SelectBuilder($manager, $reference);
        $output->setDistinct($this->distinct);
        $output->setParentQuery($this->parentQuery);
        $output->setSubQueryMode($this->subQueryMode, $this->applicator);
        $output->setDerivationParent($this->derivationParent);

        return $output;
    }
}
