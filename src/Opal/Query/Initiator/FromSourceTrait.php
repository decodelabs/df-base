<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Initiator;

use Df\Opal\Query\Initiator\FromSource;
use Df\Opal\Query\Initiator\Select;
use Df\Opal\Query\Initiator\Union;

trait FromSourceTrait
{
    protected $aliasPrefix;


    /**
     * Set alias prefix
     */
    public function setAliasPrefix(?string $prefix): FromSource
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
        $output = new Select($this->app, $fields);
        $output->setParentQuery($this->parentQuery);
        $output->setAliasPrefix(uniqid('dss_'));
        $output->setSubQueryMode('derivation');
        $output->setDerivationParent($this);

        return $output;
    }

    /**
     * Select from distinct subquery
     */
    public function fromSelectDistinct(string ...$fields): Select
    {
        $output = new Select($this->app, $fields, true);
        $output->setParentQuery($this->parentQuery);
        $output->setAliasPrefix(uniqid('dss_'));
        $output->setSubQueryMode('derivation');
        $output->setDerivationParent($this);

        return $output;
    }

    /**
     * Select from union subquery
     */
     /*
    public function fromUnion(): Union
    {
        $output = new Union($this->app);
        $output->setParentQuery($this->parentQuery);
        $output->setSubQueryMode('derivation');
        $output->setDerivationParent($this);
        return $output;
    }
    */
}
