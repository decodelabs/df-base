<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Clause;

use Df\Opal\Query\Clause\Representation;

trait RepresentationTrait
{
    protected $or = false;


    /**
     * Set this as an OR clause
     */
    public function setOr(bool $or): Representation
    {
        $this->or = $or;
        return $this;
    }

    /**
     * Is this an OR clause?
     */
    public function isOr(): bool
    {
        return $this->or;
    }

    /**
     * Is this an AND clause?
     */
    public function isAnd(): bool
    {
        return !$this->or;
    }
}
