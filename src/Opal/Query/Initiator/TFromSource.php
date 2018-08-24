<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Initiator;

use Df;

trait TFromSource
{
    /**
     * Select from subquery
     */
    public function fromSelect(string ...$fields): Select
    {
        return (new Select($this->app, $fields))
            ->setParentQuery($this->parentQuery)
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
            ->setSubQueryMode('derivation')
            ->setDerivationParent($this);
    }

    /**
     * Select from union subquery
     */
    public function fromUnion(): Union
    {
        return (new Union($this->app))
            ->setParentQuery($this->parentQuery)
            ->setSubQueryMode('derivation')
            ->setDerivationParent($this);
    }
}
