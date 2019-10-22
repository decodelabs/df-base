<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Initiator;

trait TFromSource
{
    protected $aliasPrefix;


    /**
     * Set alias prefix
     */
    public function setAliasPrefix(?string $prefix): IFromSource
    {
        $this->aliasPrefix = $prefix;
        return $this;
    }

    /**
     * Get alias prefix;
     */
    public function getAliasPrefix(): ?string
    {
        return $this->aliasPrefix;
    }


    /**
     * Select from subquery
     */
    public function fromSelect(string ...$fields): Select
    {
        return (new Select($this->app, $fields))
            ->setParentQuery($this->parentQuery)
            ->setAliasPrefix(uniqid('dss_'))
            ->setSubQueryMode('derivation')
            ->setDerivationParent($this);
    }

    /**
     * Select from distinct subquery
     */
    public function fromSelectDistinct(string ...$fields): Select
    {
        return (new Select($this->app, $fields, true))
            ->setParentQuery($this->parentQuery)
            ->setAliasPrefix(uniqid('dss_'))
            ->setSubQueryMode('derivation')
            ->setDerivationParent($this);
    }

    /**
     * Select from union subquery
     */
     /*
    public function fromUnion(): Union
    {
        return (new Union($this->app))
            ->setParentQuery($this->parentQuery)
            ->setSubQueryMode('derivation')
            ->setDerivationParent($this);
    }
    */
}
